<?php

namespace weareferal\RemoteSync\controllers;

use Craft;
use craft\web\Controller;

use weareferal\RemoteSync\RemoteSync;
use weareferal\RemoteSync\queue\PullDatabaseJob;
use weareferal\RemoteSync\queue\PullVolumeJob;
use weareferal\RemoteSync\queue\PushDatabaseJob;
use weareferal\RemoteSync\queue\PushVolumeJob;
use weareferal\RemoteSync\queue\DeleteDatabaseJob;
use weareferal\RemoteSync\queue\DeleteVolumeJob;


class RemoteSyncController extends Controller
{
    public function actionListDatabases()
    {
        $this->requireCpRequest();
        $this->requirePermission('remotesync');

        try {
            return $this->asJson([
                "backups" => RemoteSync::getInstance()->remotesync->listDatabases(),
                "success" => true
            ]);
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error getting remote database backups'));
        }
    }

    public function actionListVolumes()
    {
        $this->requireCpRequest();
        $this->requirePermission('remotesync');

        try {
            return $this->asJson([
                "backups" => RemoteSync::getInstance()->remotesync->listVolumes(),
                "success" => true
            ]);
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error listing volumes'));
        }
    }

    public function actionPushDatabase()
    {
        $this->requireCpRequest();
        $this->requirePermission('remotesync');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new PushDatabaseJob());
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->pushDatabase();
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Database pushed'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error pushing database'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    public function actionPushVolume()
    {
        $this->requirePostRequest();
        $this->requireCpRequest();
        $this->requirePermission('remotesync');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new PushVolumeJob());
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->pushVolumes();
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Volumes pushed'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error pushing volume'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    public function actionPullDatabase()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requirePermission('remotesync');

        $filename = Craft::$app->getRequest()->getRequiredBodyParam('filename');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new PullDatabaseJob([
                    'filename' => $filename
                ]));
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->pullDatabase($filename);
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Database pulled'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error pulling database'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    public function actionPullVolume()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requirePermission('remotesync');

        $filename = Craft::$app->getRequest()->getRequiredBodyParam('filename');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new PullVolumeJob([
                    'filename' => $filename
                ]));
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->pullVolume($filename);
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Volumes pulled'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error pulling volume'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }


    public function actionDeleteDatabase()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requirePermission('remotesync');

        $filename = Craft::$app->getRequest()->getRequiredBodyParam('filename');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new DeleteDatabaseJob([
                    'filename' => $filename
                ]));
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->deleteDatabase($filename);
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Database deleted'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error deleting database'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    public function actionDeleteVolume()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requirePermission('remotesync');

        $filename = Craft::$app->getRequest()->getRequiredBodyParam('filename');

        try {
            $useQueue = RemoteSync::getInstance()->getSettings()->useQueue;

            if ($useQueue) {
                Craft::$app->queue->push(new DeleteVolumeJob([
                    'filename' => $filename
                ]));
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Job added to queue'));
            } else {
                RemoteSync::getInstance()->remotesync->deleteVolume($filename);
                Craft::$app->getSession()->setNotice(Craft::t('remote-sync', 'Volumes deleted'));
            }
        } catch (\Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            return $this->asErrorJson(Craft::t('remote-sync', 'Error deleting volume'));
        }

        return $this->asJson([
            "success" => true
        ]);
    }
}