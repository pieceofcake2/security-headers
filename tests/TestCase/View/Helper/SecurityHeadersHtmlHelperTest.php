<?php

class SecurityHeadersHtmlHelperTest extends CakeTestCase
{
    private View $view;

    private SecurityHeadersHtmlHelper $Html;

    public function setUp(): void
    {
        parent::setUp();
        $this->view = new View();
        $this->view->request = new CakeRequest('/posts');
        $this->view->request->addParams([SecurityHeadersFilter::NONCE => 'abc']);
        $this->Html = new SecurityHeadersHtmlHelper($this->view);
    }

    /**
     * The scripts of script(), scriptBlock() and scriptStart() get the nonce
     *
     * @return void
     */
    public function testScriptsGetTheNonce(): void
    {
        $this->assertSame('<script src="https://cdn.example.com/a.js" nonce="abc"></script>', $this->Html->script('https://cdn.example.com/a.js'));
        $this->assertSame('<script nonce="abc">console.log(1);</script>', $this->Html->scriptBlock('console.log(1);'));

        $this->Html->scriptStart();
        echo 'console.log(2);';
        $this->assertSame('<script nonce="abc">console.log(2);</script>', $this->Html->scriptEnd());

        $this->Html->script(['https://cdn.example.com/b.js', 'https://cdn.example.com/c.js'], ['block' => 'script']);
        $this->Html->scriptBlock('console.log(3);', ['inline' => false]);
        $this->assertSame(
            '<script src="https://cdn.example.com/b.js" nonce="abc"></script>'
            . '<script src="https://cdn.example.com/c.js" nonce="abc"></script>'
            . '<script nonce="abc">console.log(3);</script>',
            $this->view->fetch('script'),
        );
        $this->Html->script('https://cdn.example.com/d.js', false);
        $this->assertStringContainsString('<script src="https://cdn.example.com/d.js" nonce="abc"></script>', $this->view->fetch('script'));
    }

    /**
     * A nonce option replaces the nonce, and false leaves it out
     *
     * @return void
     */
    public function testNonceOption(): void
    {
        $this->assertSame('<script nonce="xyz">1</script>', $this->Html->scriptBlock('1', ['nonce' => 'xyz']));
        $this->assertSame('<script>1</script>', $this->Html->scriptBlock('1', ['nonce' => false]));
    }

    /**
     * Without the nonce, e.g. when contentSecurityPolicyNonce is off, the scripts have no nonce attribute
     *
     * @return void
     */
    public function testWithoutTheNonce(): void
    {
        $view = new View();
        $view->request = new CakeRequest('/posts');
        $Html = new SecurityHeadersHtmlHelper($view);

        $this->assertSame('<script>1</script>', $Html->scriptBlock('1'));
        $this->assertSame('<script>1</script>', (new SecurityHeadersHtmlHelper(new View()))->scriptBlock('1'));
    }

    /**
     * The helper replaces Html with className, so that the views and the other helpers use it
     *
     * @return void
     */
    public function testAsHtml(): void
    {
        $this->view->Helpers->load('Html', ['className' => 'SecurityHeaders.SecurityHeadersHtml']);

        $this->assertInstanceOf(SecurityHeadersHtmlHelper::class, $this->view->Html);
        $this->assertSame('<script nonce="abc">1</script>', $this->view->Html->scriptBlock('1'));
    }
}
