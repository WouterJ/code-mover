<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;
use DTL\CodeMover\MoverLine;

class MoverFile extends ArrayCollection
{
    protected $_initialized = false;
    protected $file;
    protected $private;
    protected $lines;
    protected $originalFile;

    public function __construct($file)
    {
        $this->lines = new ArrayCollection();
        $this->file = $file;
    }

    public function getLines()
    {
        $this->init();
        return $this->lines;
    }

    public function nameMatches($pattern)
    {
        return (boolean) preg_match($pattern, $this->file);
    }

    public function init()
    {
        if ($this->_initialized) {
            return;
        }

        $this->originalFile = file($this->file);
        foreach ($this->originalFile as $fileLine) {
            $this->lines->add(new MoverLine($this, $fileLine));
        }

        $this->_initialized = true;
    }

    public function findLine($pattern)
    {
        foreach ($this->getLines() as $line) {
            if ($line->match($pattern)) {
                return $line;
            }
        }

        return null;
    }

    public function findLines($patterns)
    {
        $patterns = (array) $patterns;

        $lines = $this->getLines()->filter(function ($line) use ($patterns) {
            $match = false;
            foreach ($patterns as $pattern) {
                if ($line->match($pattern)) {
                    $match = true;
                    break;
                }
            }

            return $match;
        });

        return $lines;
    }

    public function dump()
    {
        return implode("", $this->getLines()->toArray());
    }

    public function getOriginalFile()
    {
        return $this->originalFile;
    }

    public function commit()
    {
        $this->originalFile = $this->lines->toArray();
    }

    public function isModified()
    {
        if ($this->originalFile == $this->lines->toArray()) {
            return false;
        }

        return true;
    }
}