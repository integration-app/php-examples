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

        function configureFlowInstance(flowInstanceId) {
            iApp.flowInstance(flowInstanceId).openConfiguration()
        }

        async function toggleFlowInstance(flowKey, currentState) {
            let flowInstance = await iApp.flowInstance({
                integrationKey: "<?php echo($_GET["key"]) ?>",
                flowKey: flowKey,
                autoCreate: true,
            })
            // Get & auto-Create flow Instance if it not exists for this User yet
            await flowInstance.get()

            // Enable / Disable Flow Instance
            await flowInstance.patch({
                enabled: !currentState
            })

            window.location.reload()
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
        <?php
        // Fetch all Integration Flows
        // Flows encapsulate integration logic out of other Integration Elements.
        // https://console.integration.app/docs/flows

        $flowsRequest = new Request('GET', 'https://engine-api.integration.app/flows', [
            'Authorization' => sprintf('Bearer %s', $integrationAppAccessToken),
        ]);
        $flowsResponse = $httpClient->send($flowsRequest);
        $flows = json_decode($flowsResponse->getBody())->items;


        // Fetch all Integration Flows Instance for Current Integration
        // Flow Instance is a Flow applied to a specific Connection.
        // https://console.integration.app/docs/flows/flow-instances

        $flowInstancesRequest = new Request('GET', 'https://engine-api.integration.app/flow-instances', [
            'Authorization' => sprintf('Bearer %s', $integrationAppAccessToken),
        ]);
        $flowInstancesResponse = $httpClient->send($flowInstancesRequest);

        $flowInstances = json_decode($flowInstancesResponse->getBody())->items;

        foreach ($flows as &$flow) {
            foreach ($flowInstances as $flowInstance) {
                if (($flowInstance->flowId) == ($flow->id)) {
                    $flow->instance = $flowInstance;
                }
            }
        }
        ?>
        <div>
            <table class="table w-full">
                <tbody>
                <?php foreach ($flows as &$flow) { ?>
                    <tr>
                        <th>
                            <?php if (isset($flow->instance)) { ?>
                                <input type="checkbox"
                                       class="toggle" <?php echo(($flow->instance->enabled) ? 'checked' : '') ?>
                                       onclick="toggleFlowInstance('<?php echo($flow->key) ?>', <?php echo($flow->instance->enabled) ?>)"
                                />

                            <?php } else { ?>
                                <input type="checkbox" class="toggle"
                                       onclick="toggleFlowInstance('<?php echo($flow->key) ?>', false)"/>
                            <?php } ?>
                        </th>
                        <td><?php echo($flow->name) ?></td>
                        <td>
                            <?php if (isset($flow->instance) && $flow->instance->enabled) { ?>
                                <button class="btn btn-outline btn-sm m-2"
                                        onclick="configureFlowInstance('<?php echo($flow->instance->id) ?>')">Configure
                                </button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
