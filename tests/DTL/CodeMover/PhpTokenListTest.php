<?php

namespace DTL\CodeMover;

use DTL\CodeMover\PhpTokenList;

class PhpTokenListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\MoverLine')
            ->disableOriginalConstructor()->getMock();
        $this->t1 = new PhpToken($this->line, 'FOOBAR', 'arf');
        $this->t2 = new PhpToken($this->line, 'BARFOO', 'barf');
        $this->t3 = new PhpToken($this->line, 'FOOBAR', 'garf');
    }

    public function testFilterByType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList = $tokenList->filterByType('FOOBAR');

        $this->assertEquals(array($this->t1, $this->t3), array_values($tokenList->toArray()));
    }

    public function testValuesByType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $values = $tokenList->valuesByType('FOOBAR');

        $this->assertEquals(array(
            'arf', 'garf'
        ), $values);
    }

    public function testSeekType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->seekType('BARFOO')->token();
        $this->assertEquals('barf', $token->getValue());
    }

    public function testSeekValue()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->seekValue('barf')->token();
        $this->assertEquals('BARFOO', $token->getType());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekValueNotFound()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList->seekValue('NOTKNOWN');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekTypeNotFound()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList->seekType('NOTKNOWN');
    }

    public function testLines()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $lines = $tokenList->lines();
        $this->assertNotNull($lines);
        $this->assertCount(1, $lines);
        $this->assertSame($this->line, $lines->first());
    }

    public function testToken()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->token();
        $this->assertSame($token, $this->t1);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No token found at offset
     */
    public function testTokenNotFound()
    {
        $tokenList = new PhpTokenList();
        $token = $tokenList->token();
    }
}
