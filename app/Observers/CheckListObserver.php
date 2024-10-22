<?php

namespace App\Observers;

use App\Models\CheckList;

class CheckListObserver
{
    /**
     * Handle the CheckList "created" event.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return void
     */
    public function created(CheckList $checkList)
    {
        //
    }

    /**
     * Handle the CheckList "updated" event.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return void
     */
    public function updated(CheckList $checkList)
    {
        //
    }

    /**
     * Handle the CheckList "deleted" event.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return void
     */
    public function deleted(CheckList $checkList)
    {
        $checkList->collaborators()->delete();
    }

    public function deleting(CheckList $checkList)
    {
        foreach ($checkList->collaborators() as $collaborator) {
            $collaborator->delete();
        }
    }

    /**
     * Handle the CheckList "restored" event.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return void
     */
    public function restored(CheckList $checkList)
    {
        //
    }

    /**
     * Handle the CheckList "force deleted" event.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return void
     */
    public function forceDeleted(CheckList $checkList)
    {
        //
    }
}
