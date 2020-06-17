<?php

namespace weareferal\remotesync\controllers;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

use Craft;
use craft\web\Controller;
use craft\web\View;

use weareferal\remotesync\exceptions\ProviderException;
use weareferal\remotesync\RemoteSync;


class GoogleDriveController extends Controller
{
    /**
     * Require the plugin to have been set as enabled in the settings
     */
    public function requirePluginEnabled()
    {
        if (!RemoteSync::getInstance()->getSettings()->enabled) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }
    }

    /**
     * Require that Google Drive has been selected as the current provider
     *
     */
    public function requireGoogleDriveProvider()
    {
        if (!RemoteSync::getInstance()->getSettings()->cloudProvider == 'google') {
            throw new BadRequestHttpException('Google Drive provider not selected');
        }
    }

    /**
     * Auth with Google
     * 
     * https://developers.google.com/docs/api/quickstart/php
     */
    public function actionAuth()
    {
        $this->requireCpRequest();
        $this->requirePermission('remotesync');
        $this->requirePluginEnabled();
        $this->requireGoogleDriveProvider();

        $plugin = RemoteSync::getInstance();
        $service = $plugin->provider;
        $client = $service->getClient();
        $isExpired = $client->isAccessTokenExpired();

        // Redirect back to settings page
        function redirect()
        {
            Craft::$app->session->setFlash('notice', Craft::t("remote-sync", "Google Drive already authenticated"));
            return $this->redirect("/admin/settings/plugins/remote-sync");
        }

        // Try refresh token 
        if ($isExpired) {
            $refreshToken = $client->getRefreshToken();
            if ($refreshToken != null) {
                $client->fetchAccessTokenWithRefreshToken($refreshToken);
                $isExpired = false;
            }
        }

        if (!$isExpired) {
            Craft::$app->session->setFlash('notice', Craft::t("remote-sync", "Google Drive already authenticated"));
            return $this->redirect("/admin/settings/plugins/remote-sync");
        }

        $externalOAuthUrl = $client->createAuthUrl();

        return $this->renderTemplate('remote-sync/google-drive/auth.twig', [
            'plugin' => $plugin,
            'url' => $externalOAuthUrl
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * Handle Google auth response
     * 
     * Save the access token that is returned via the code parameter and save
     * it to the settings
     */
    public function actionAuthRedirect()
    {
        $this->requireCpRequest();
        $this->requirePermission('remotesync');
        $this->requirePluginEnabled();
        $this->requireGoogleDriveProvider();

        $plugin = RemoteSync::getInstance();
        $service = $plugin->provider;

        $code = Craft::$app->getRequest()->get('code');
        if (!$code) {
            throw new NotFoundHttpException();
        }

        $client = $service->getClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($code);
        $client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new ProviderException(join(', ', $accessToken));
        }

        // Save the access token to the storage folder
        $tokenPath = $service->getTokenPath();
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));

        return $this->renderTemplate('remote-sync/google-drive/auth-redirect.twig', [
            'plugin' => $plugin
        ], View::TEMPLATE_MODE_CP);
    }
}
