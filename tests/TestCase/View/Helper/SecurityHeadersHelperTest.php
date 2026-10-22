<?php

class SecurityHeadersHelperTest extends CakeTestCase
{
    public function testNonce(): void
    {
        $view = new View();
        $view->request = new CakeRequest('/posts');
        $helper = new SecurityHeadersHelper($view);

        $this->assertSame('', $helper->nonce());
        $view->request->addParams([SecurityHeadersFilter::NONCE => 'abc']);
        $this->assertSame('abc', $helper->nonce());
    }

    public function testNoRequest(): void
    {
        $this->assertSame('', (new SecurityHeadersHelper(new View()))->nonce());
    }
}
