<?php

namespace weareferal\remotesync\queue;

use craft\queue\BaseJob;
use yii\queue\RetryableJobInterface;

use weareferal\remotesync\RemoteSync;


class DeleteDatabaseJob extends BaseJob implements RetryableJobInterface
{
    public $filename;

    public function getTtr()
    {
        return RemoteSync::getInstance()->getSettings()->queueTtr;
    }

    public function execute($queue)
    {
        RemoteSync::getInstance()->provider->deleteDatabase($this->filename);
    }

    protected function defaultDescription()
    {
        return 'Delete remote database';
    }

    public function canRetry($attempt, $error)
    {
        return true;
    }
}
