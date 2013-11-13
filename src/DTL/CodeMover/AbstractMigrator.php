<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverContext;

abstract class AbstractMigrator implements MigratorInterface
{
    use LoggableTrait;

    protected $context;

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(MoverContext $context)
    {
        $this->context = $context;
    }
}
