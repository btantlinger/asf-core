<?php namespace asf\data;
/**
 *
 * @author bob
 */
interface ListData extends \Iterator {
    public function nextVal();
    public function size();
}
//EOF