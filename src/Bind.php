<?php

namespace SQLBuilder;

class Bind
{
    protected $name;

    protected $value;

    protected $valueForQuery;

    protected $differentForQuery = false;

    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setForQuery($escaped) {
        $this->valueForQuery = $escaped;
        $this->differentForQuery = true;
    }

    public function getForQuery() {
        return $this->differentForQuery ? $this->valueForQuery : $this->getValue();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMarker()
    {
        return ':'.$this->name;
    }

    /**
     * The compare method only compares value.
     */
    public function compare(Bind $b)
    {
        return $this->value === $b->value;
    }

    public static function bindArray(array $array)
    {
        $args = array();
        foreach ($array as $key => $value) {
            $args[$key] = new self($key, $value);
        }

        return $args;
    }
}
