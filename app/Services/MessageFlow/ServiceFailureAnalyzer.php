<?php

namespace App\Services\MessageFlow;

use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Models\MessageFlow;
use Illuminate\Support\Collection;

class ServiceFailureAnalyzer
{
    /**
     * Analyze which services might be failing based on flow patterns
     */
    public function analyzePotentialFailures(Collection $flows): Collection
    {
        $failedFlows = $flows->filter(fn(MessageFlow $flow) => $flow->isFailed());
        
        if ($failedFlows->isEmpty()) {
            return collect();
        }

        $potentialFailures = collect();

        // Check ThingsBoard failures
        if ($this->isThingsBoardFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::THINGSBOARD);
        }

        // Check ChirpStack failures
        if ($this->isChirpStackFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::CHIRPSTACK);
        }

        // Check MQTT failures
        if ($this->isMqttTbFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::MQTT_TB);
        }
        if ($this->isMqttCsFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::MQTT_CS);
        }

        // Check LoRa failures
        if ($this->isLoraTXFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::LORATX);
        }
        if ($this->isLoraRXFailure($failedFlows)) {
            $potentialFailures->push(ServiceType::LORARX);
        }

        return $potentialFailures->unique();
    }

    private function isThingsBoardFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            in_array($flow->flow_type, [
                FlowType::TB_MQTT_HEALTH,
                // FlowType::TB_HTTP_HEALTH,
                FlowType::TB_TO_CS,
                FlowType::CS_TO_TB_TO_CS
            ])
        );
    }

    private function isChirpStackFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            in_array($flow->flow_type, [
                FlowType::CS_MQTT_HEALTH,
                // FlowType::CS_HTTP_HEALTH,
                FlowType::CS_TO_TB,
                FlowType::CS_TO_TB_TO_CS
            ])
        );
    }

    private function isMqttTbFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            $flow->flow_type === FlowType::TB_MQTT_HEALTH
        );
    }

    private function isMqttCsFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            $flow->flow_type === FlowType::CS_MQTT_HEALTH
        );
    }

    private function isLoraTXFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            in_array($flow->flow_type, [
                FlowType::TB_TO_CS,
                FlowType::CS_TO_TB_TO_CS
            ])
        );
    }

    private function isLoraRXFailure(Collection $failedFlows): bool
    {
        return $failedFlows->contains(fn(MessageFlow $flow) => 
            in_array($flow->flow_type, [
                FlowType::CS_TO_TB,
                FlowType::CS_TO_TB_TO_CS
            ])
        );
    }
}
