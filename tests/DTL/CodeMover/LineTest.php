<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Line;

class LineTest extends \PHPUnit_Framework_TestCase
{
    protected $moverFile;

    public function setUp()
    {
        $this->moverFile = $this->getMockBuilder(
            'DTL\CodeMover\AbstractFile'
        )->disableOriginalConstructor()->getMock();
    }

    public function provideMatches()
    {
        return array(
            array('namespace Foo', '/namespace/', true),
            array('namespace Foo', '/.*namespace.*Foo/', true),
            array('foobar', '/.*namespace/', false),
        );
    }

    /**
     * @dataProvider provideMatches
     */
    public function testMatches($line, $pattern, $isMatch)
    {
        $line = new Line($this->moverFile, $line);
        $res = $line->matches($pattern);
        $this->assertEquals($isMatch, $res);
    }

    public function provideMatch()
    {
        return array(
            array('namespace Foo', 'namespace (.*)', array('namespace Foo', 'Foo')),
        );
    }

    /**
     * @dataProvider provideMatch
     */
    public function testMatch($line, $pattern, $expectedMatches)
    {
        $line = new Line($this->moverFile, $line);
        $res = $line->match($pattern);
        $matches = $res->getMatches();

        $this->assertNotNull($matches);
        $this->assertEquals($expectedMatches, $matches);
        $this->assertSame($line, $res->getLine());

        foreach ($expectedMatches as $i => $expected) {
            $this->assertEquals($expected, $res->getMatch($i));
        }
    }

    public function provideReplace()
    {
        return array(
            array('The quick brown fox', '/fox/', 'bear', 'The quick brown bear'),
            array('The quick brown fox', array('/dabd/', '/fox/'), 'bear', 'The quick brown bear'),
            array('The quick brown fox', array('/dabd/', '/fox/'), 'bear', 'The quick brown bear'),
            array('The quick brown fox', '/quick (.*) fox/', function ($matches) {
                return 'slow '.$matches[1].' elephant';
            }, 'The slow brown elephant'),
        );
    }

    /**
     * @dataProvider provideReplace
     */
    public function testReplace($line, $pattern, $replacement, $expected)
    {
        $line = new Line($this->moverFile, $line);
        $res = $line->replace($pattern, $replacement);
        $this->assertEquals($expected, (string) $line);
        $this->assertSame($line, $res, 'Fluid interface OK');
    }

    public function testDelete()
    {
        $line = new Line($this->moverFile, 'This is a line');
        $this->moverFile->expects($this->once())
            ->method('remove')
            ->with($line)
            ->will($this->returnValue(true));
        $res = $line->delete();
        $this->assertSame($line, $res, 'Fluid interface OK');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not delete element
     */
    public function testDeleteNull()
    {
        $line = new Line($this->moverFile, 'This is a line');
        $res = $line->delete();
    }

    public function testTokenize()
    {
        $line = new Line($this->moverFile, '$this;');
        $tokenList = $line->tokenize()->getTokensAsArray();
        $this->assertEquals(array(
            array('VARIABLE', '$this'),
            array('SINGLE_CHAR', ';'),
        ), $tokenList);
    }

    public function provideNextPrevLine()
    {
        return array(
            array('next'),
            array('prev'),
        );
    }

    /**
     * @dataProvider provideNextPrevLine
     */
    public function testNextPrevLine($type)
    {
        $line = new Line($this->moverFile, 'Line the Central');
        $siblingLine = new Line($this->moverFile, 'Line the sibling');
        $offset = $type == 'next' ? 6 : 4;

        $this->moverFile->expects($this->once())
            ->method('getLineNeighbor')
            ->with($line, $type == 'next' ? null : true)
            ->will($this->returnValue(5));

        if ($type == 'next') {
            $res = $line->nextLine();
        } else {
            $res = $line->prevLine();
        }
    }

    public function testNextPrevLineNull()
    {
        $line = new Line($this->moverFile, 'Line the Central');
        $this->assertNull($line->nextLine());
        $this->assertNull($line->prevLine());
    }

    public function testHasChanged()
    {
        $line = new Line($this->moverFile, 'Line the Original');
        $line->setLine('Line the Second');
        $res = $line->hasChanged();

        $this->assertTrue($res);

        $originalLine = $line->getOriginalLine();
        $this->assertEquals('Line the Original', $originalLine);
    }

    public function testTokenizeStatement()
    {
        $l1 = new Line($this->moverFile, '$options = array(');
        $l2 = new Line($this->moverFile, '  "foobar",');
        $l3 = new Line($this->moverFile, ');');

        $this->moverFile->expects($this->exactly(2))
            ->method('getLineNeighbor')
            ->will($this->onConsecutiveCalls(
                $this->returnValue($l2),
                $this->returnValue($l3)
            ));

        $tokenList = $l1->tokenizeStatement();
        $this->assertEquals('$options = array(  "foobar",);', implode('', $tokenList->getvalues()));
    }

    public function testTokenizeStatementNoTerminator()
    {
        $l1 = new Line($this->moverFile, '$options = array(');

        $this->moverFile->expects($this->exactly(1))
            ->method('getLineNeighbor')
            ->will($this->returnValue(null));

        $tokens = $l1->tokenizeStatement();
        $this->assertEquals('$options = array(', implode('', $tokens->getValues()));
    }
}














