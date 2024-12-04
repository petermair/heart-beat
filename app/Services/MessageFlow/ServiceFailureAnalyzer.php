<?php

namespace App\Services\MessageFlow;

use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Enums\TestResultStatus;
use App\Models\MessageFlow;
use Illuminate\Support\Collection;

class ServiceFailureAnalyzer
{
    /**
     * Matrix defining which flows must fail (true) or succeed (false) for each service to be considered down
     */
    public const SERVICE_MATRIX_FLOW_FAIL = [
        ServiceType::THINGSBOARD->value => [
            FlowType::TB_TO_CS->value => true,
            FlowType::CS_TO_TB->value => true,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => true,
            FlowType::DIRECT_TEST_TB_CS->value => true,
            FlowType::TB_MQTT_HEALTH->value => true,
            FlowType::CS_MQTT_HEALTH->value => false
        ],
        ServiceType::CHIRPSTACK->value => [
            FlowType::TB_TO_CS->value => true,
            FlowType::CS_TO_TB->value => true,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => true,
            FlowType::DIRECT_TEST_TB_CS->value => true,
            FlowType::TB_MQTT_HEALTH->value => false,
            FlowType::CS_MQTT_HEALTH->value => true
        ],
        ServiceType::MQTT_TB->value => [
            FlowType::TB_TO_CS->value => true,
            FlowType::CS_TO_TB->value => true,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => false,
            FlowType::DIRECT_TEST_TB_CS->value => false,
            FlowType::TB_MQTT_HEALTH->value => true,
            FlowType::CS_MQTT_HEALTH->value => false
        ],
        ServiceType::MQTT_CS->value => [
            FlowType::TB_TO_CS->value => true,
            FlowType::CS_TO_TB->value => true,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => false,
            FlowType::DIRECT_TEST_TB_CS->value => false,
            FlowType::TB_MQTT_HEALTH->value => false,
            FlowType::CS_MQTT_HEALTH->value => true
        ],
        ServiceType::LORATX->value => [
            FlowType::TB_TO_CS->value => true,
            FlowType::CS_TO_TB->value => false,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => false,
            FlowType::DIRECT_TEST_TB_CS->value => false,
            FlowType::TB_MQTT_HEALTH->value => false,
            FlowType::CS_MQTT_HEALTH->value => false
        ],
        ServiceType::LORARX->value => [
            FlowType::TB_TO_CS->value => false,
            FlowType::CS_TO_TB->value => true,
            FlowType::CS_TO_TB_TO_CS->value => true,
            FlowType::DIRECT_TEST_CS_TB->value => false,
            FlowType::DIRECT_TEST_TB_CS->value => false,
            FlowType::TB_MQTT_HEALTH->value => false,
            FlowType::CS_MQTT_HEALTH->value => false
        ]
    ];

    /**
     * Analyze flows to determine which service is definitely down
     */
    public function analyzePotentialFailures(Collection $flows): Collection
    {
        foreach (ServiceType::cases() as $service) {
            $isServiceDown = true;

            // Check each flow in the matrix for this service
            foreach (self::SERVICE_MATRIX_FLOW_FAIL[$service->value] as $flowType => $mustFail) {
                $flow = $flows->first(fn($f) => $f->flow_type->value === $flowType);
                if (!$flow) continue;

                $isFailing = $flow->status === TestResultStatus::FAILURE->value;
                
                // If a flow that must fail is successful, or a flow that must succeed is failing
                if (($mustFail && !$isFailing) || (!$mustFail && $isFailing)) {
                    $isServiceDown = false;
                    break;
                }
            }

            if ($isServiceDown) {
                return collect([$service]);
            }
        }

        return collect();
    }
}
