<?php

declare(strict_types=1);

namespace Firezihai\Sqlite\Query;

class IndexHint
{
    /**
     * The type of query hint.
     *
     * @var string
     */
    public $type;

    /**
     * The name of the index.
     *
     * @var string
     */
    public $index;

    /**
     * Create a new index hint instance.
     *
     * @param string $type
     * @param string $index
     */
    public function __construct($type, $index)
    {
        $this->type = $type;
        $this->index = $index;
    }
}
