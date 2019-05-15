<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dylan
 * Date: 15/05/2019
 * Time: 9:20 AM
 */

namespace PhpAccounting\Common\Interfaces;


interface CrudInterface
{

    public function create();
    public function update();
    public function get();
    public function delete();
}