<?php

namespace App\Supports\Arrays;

class Unique
{
    function collaborators($array)
    {
        $countedArray = array();

        foreach ($array as $item) {
            $collaborator_id = $item['collaborator_id'];

            if (isset($countedArray[$collaborator_id])) {
                $countedArray[$collaborator_id] = $item;
            } else {
                $countedArray[$collaborator_id] = $item;
            }
        }

        return $countedArray;
    }
}
