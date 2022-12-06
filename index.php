<?php

require_once 'vendor/autoload.php';
require_once 'integrationAppToken.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$httpClient = new Client();
$integrationAppAccessToken = getIntegrationAppToken();
?>

<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.1.slim.min.js"
            integrity="sha256-w8CvhFs7iHNVUtnSP0YKEg00p9Ih13rlL9zGqvLdePA=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.42.0/dist/full.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2/dist/tailwind.min.css" rel="stylesheet" type="text/css"/>

    <!-- Import Integration.app SDK -->
    <script src="https://unpkg.com/@integration-app/sdk@0.16.17/bundle.js"></script>

    <script>
        // Initialize IntegrationApp SDK using token that we've generated on the backend
        iApp.init({token: "<?php echo($integrationAppAccessToken) ?>",})

        async function connectIntegration(integrationKey) {
            await iApp.integration(integrationKey).connect()
            location.reload()
        }

        async function disconnectIntegration(connectionId) {
            await iApp.connection(connectionId).archive()
            location.reload()
        }

    </script>
</head>
<body class="antialiased">
<div class="mockup-window border bg-base-300" style="max-width: 900px; margin: 4rem auto;">
    <div class="navbar bg-base-100">
        <div class="flex-1">
            <a class="btn btn-ghost normal-case text-xl">OTA Sync</a>
            <ul class="menu menu-horizontal p-0">
                <li><a href="/index.php">Integrations</a></li>
            </ul>
        </div>
    </div>
    <div class="flex justify-center px-4 py-16 bg-base-200">
        <div>
            <table class="table w-full">
                <tbody>
            <?php


            $request = new Request('GET', 'https://engine-api.integration.app/integrations', [
                'Authorization' => sprintf('Bearer %s', $integrationAppAccessToken),
            ]);
            $response = $httpClient->send($request);
            $integrationsArray = json_decode($response->getBody())->items;

            foreach ($integrationsArray as $integration) {
                ?>
                <tr>
                    <td>
                        <div className="avatar">
                            <div className=" w-12 h-12">
                                <img src="<?php echo($integration->logoUri)?>" style="max-height: 50px"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php echo($integration->name) ?>
                    </td>
                    <td>
                        <?php if (property_exists($integration,'connection')) { ?>
                            <a href="./integrationPage.php?key=<?php echo($integration->key); ?>">
                                <button class="btn btn-outline btn-sm m-2" onclick="">Configure</button>
                            </a>
                            <button class="btn btn-outline btn-sm m-2"
                                    onclick="disconnectIntegration('<?php echo($integration->connection->id); ?>')">Disconnect
                            </button>
                        <?php } else { ?>
                            <button class="btn btn-outline btn-sm m-2"
                                    onclick="connectIntegration('<?php echo($integration->key); ?>')">Connect
                            </button>
                        <?php } ?>
                    </td>

                </tr>

                <?php
            }
            ?>        </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
