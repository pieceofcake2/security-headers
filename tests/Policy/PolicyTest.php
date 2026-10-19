<?php

/**
 * Opens the embed code or the base code of each service with the enforced policy of its classes in a headless
 * Chrome, through the services on the Internet: the page must load the files of the service, and the browser must
 * report no violation of the policy. The services change their hosts from time to time, so this runs on a schedule
 * (.github/workflows/policies.yml), not with the other tests.
 *
 * ```
 * vendor/bin/phpunit tests/Policy
 * ```
 *
 * `CHROME_BINARY` sets the binary of Chrome or Chromium.
 */
class PolicyTest extends CakeTestCase
{
    /**
     * Seconds of the virtual time of Chrome for a page
     */
    private const BUDGET = 30;

    /**
     * Attempts of a page, as the services sometimes answer slowly
     */
    private const ATTEMPTS = 2;

    /**
     * The built-in web server
     *
     * @var resource|null
     */
    private static $server = null;

    private static int $port = 0;

    private static string $reports = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::$port = (int)substr((string)stream_socket_get_name($socket, false), strrpos((string)stream_socket_get_name($socket, false), ':') + 1);
        fclose($socket);
        self::$reports = (string)tempnam(sys_get_temp_dir(), 'policy-reports');
        self::$server = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . self::$port, __DIR__ . DS . 'router.php'],
            [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            __DIR__,
            ['SECURITY_HEADERS_REPORTS' => self::$reports] + getenv(),
        ) ?: null;
        // Waits for the web server, without the warnings of the connections that it refuses meanwhile
        set_error_handler(static fn(): bool => true);
        try {
            for ($i = 0; $i < 50; $i++) {
                $connection = fsockopen('127.0.0.1', self::$port, $errno, $error, 0.1);
                if ($connection !== false) {
                    fclose($connection);
                    break;
                }
                usleep(100000);
            }
        } finally {
            restore_error_handler();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
            self::$server = null;
        }
        if (is_file(self::$reports)) {
            unlink(self::$reports);
        }
        parent::tearDownAfterClass();
    }

    /**
     * The pages of pages.php
     *
     * @return array<string, array{0: string}>
     */
    public static function pages(): array
    {
        $pages = [];
        foreach (array_keys(require __DIR__ . DS . 'pages.php') as $name) {
            $pages[$name] = [$name];
        }

        return $pages;
    }

    /**
     * @dataProvider pages
     * @param string $name Page of pages.php
     * @return void
     */
    public function testPolicy(string $name): void
    {
        for ($attempt = 1; $attempt <= self::ATTEMPTS; $attempt++) {
            file_put_contents(self::$reports, '');
            $title = $this->open($name);
            $violations = $this->violations($name);
            if ($title === 'ready' && $violations === []) {
                break;
            }
        }

        $this->assertSame([], $violations, sprintf('The policy of "%s" blocks these, add them to its classes', $name));
        $this->assertSame('ready', $title, sprintf('"%s" did not load all its files', $name));
    }

    /**
     * Opens a page in a headless Chrome
     *
     * @param string $name Page
     * @return string The title of the page: "ready" when it loaded all its files
     */
    private function open(string $name): string
    {
        $profile = sys_get_temp_dir() . DS . 'policy-chrome-' . uniqid();
        $process = proc_open(
            [
                $this->chrome(),
                '--headless=new',
                '--disable-gpu',
                '--no-sandbox',
                '--no-first-run',
                '--user-data-dir=' . $profile,
                '--virtual-time-budget=' . (self::BUDGET * 1000),
                '--dump-dom',
                'http://127.0.0.1:' . self::$port . '/' . $name,
            ],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
        );
        $this->assertIsResource($process, 'Chrome does not start');
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        $dom = '';
        $deadline = microtime(true) + self::BUDGET * 3;
        while (microtime(true) < $deadline) {
            $dom .= (string)stream_get_contents($pipes[1]);
            if (!proc_get_status($process)['running']) {
                break;
            }
            usleep(100000);
        }
        $dom .= (string)stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_terminate($process);
        proc_close($process);
        exec('rm -rf ' . escapeshellarg($profile));

        return preg_match('#<title>(.*?)</title>#s', $dom, $matches) ? html_entity_decode($matches[1]) : '';
    }

    /**
     * The violations that the browser reported for a page
     *
     * @param string $name Page
     * @return array<int, string> "directive blocked-uri"
     */
    private function violations(string $name): array
    {
        $violations = [];
        foreach (file(self::$reports, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $entry = json_decode($line, true);
            $report = $entry['report']['csp-report'] ?? [];
            if (($entry['page'] ?? '') === $name) {
                $violations[] = ($report['violated-directive'] ?? '?') . ' ' . ($report['blocked-uri'] ?? '?');
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * The binary of Chrome: `CHROME_BINARY`, or the first one found
     *
     * @return string
     */
    private function chrome(): string
    {
        $binary = getenv('CHROME_BINARY');
        if (is_string($binary) && $binary !== '') {
            return $binary;
        }
        foreach (['google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser'] as $command) {
            $path = trim((string)shell_exec('command -v ' . $command . ' 2>/dev/null'));
            if ($path !== '') {
                return $path;
            }
        }
        $mac = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
        if (is_file($mac)) {
            return $mac;
        }
        $this->markTestSkipped('Chrome is not found: set CHROME_BINARY.');
    }
}
