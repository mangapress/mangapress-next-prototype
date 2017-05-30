<?php
class WP_UnitTest_Generator_Date_Sequence extends WP_UnitTest_Generator_Sequence
{
    function next() {
        $generated = date('Y-m-d H:i:s', strtotime(sprintf( $this->template_string , $this->next )));
        var_dump($generated); die();
        $this->next++;
        return $generated;
    }
}