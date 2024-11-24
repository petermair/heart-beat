# Service Detail Page Mockup

## ThingsBoard Service Detail
```
┌──────────────────────────────────────────────────────────────────────────────┐
│ ThingsBoard Service                                               [Refresh]   │
├──────────────────────────────────────────────────────────────────────────────┤
│ ┌─Basic Info──────────────────┐ ┌─Quick Stats (Last 24h)──────────────────┐ │
│ │ Status: 🟢 Healthy          │ │ Messages Processed: 1,234               │ │
│ │ Uptime: 99.9% (24h)         │ │ Success Rate: 98.5%                     │ │
│ │ Last Message: 2m ago        │ │ Avg Response: 235ms                     │ │
│ └───────────────────────────────┘ └────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────────────────────────────┤
│ Active Flows                                                    [Expand All]  │
├──────────────────────────────────────────────────────────────────────────────┤
│ ┌─Flow 1: TB → MQTT → LoraTX → MQTT → CS───────────────────────┐ [Details]  │
│ │ Status: 🟢  Success: 99%  Last: 2m ago  Msgs: 532                        │ │
│ └─────────────────────────────────────────────────────────────────────────┘  │
│ ┌─Flow 3: Two-way Route────────────────────────────────────────┐ [Details]  │
│ │ Status: 🟡  Success: 85%  Last: 5m ago  Msgs: 231                        │ │
│ └─────────────────────────────────────────────────────────────────────────┘  │
│ ┌─Flow 4: Direct Test (CS → MQTT → TB)───────────────────────┐ [Details]   │
│ │ Status: 🟢  Success: 100%  Last: 1m ago  Msgs: 123                       │ │
│ └─────────────────────────────────────────────────────────────────────────┘  │
│ [Show More Flows...]                                                         │
├──────────────────────────────────────────────────────────────────────────────┤
│ Statistics                                         [1h] [24h] [7d] [Custom]   │
├──────────────────────────────────────────────────────────────────────────────┤
│ ┌─Message Volume────────┐ ┌─Success Rate────────┐ ┌─Response Time─────────┐ │
│ │      ▅ ▆ ▅ ▄ ▆ ▇ █   │ │   ▆ ▇ █ █ █ ▇ █    │ │    ▃ ▄ ▅ ▄ ▃ ▂ ▃     │ │
│ │      1h ago → Now    │ │   1h ago → Now      │ │    1h ago → Now       │ │
│ └──────────────────────┘ └────────────────────┘ └─────────────────────────┘ │
├──────────────────────────────────────────────────────────────────────────────┤
│ Recent Issues                                                    [View All]   │
├──────────────────────────────────────────────────────────────────────────────┤
│ 🔴 Flow 3 failed - Timeout after 5s         2024-01-20 15:23:45             │
│ 🟡 High latency detected - 2.3s response    2024-01-20 15:20:12             │
│ 🔴 Connection dropped - Reconnected         2024-01-20 15:15:33             │
│ [Show More...]                                                               │
└──────────────────────────────────────────────────────────────────────────────┘
```

## Interaction Details

### Flow Details Popup (when clicking [Details])
```
┌─Flow 1: TB → MQTT → LoraTX → MQTT → CS─────────────────────────┐
│ Current Status: 🟢 Healthy                                      │
│                                                                │
│ Message Flow Diagram:                                          │
│ ThingsBoard → MQTT → LoraTX → MQTT → ChirpStack               │
│     🟢         🟢      🟢      🟢       🟢                    │
│                                                                │
│ Statistics (Last Hour):                                        │
│ ├─ Messages Sent: 532                                          │
│ ├─ Success Rate: 99%                                           │
│ ├─ Avg Response: 235ms                                         │
│ └─ Failed Messages: 5                                          │
│                                                                │
│ Recent Messages:                                               │
│ 15:30:45 - Success - 230ms                                     │
│ 15:29:30 - Success - 242ms                                     │
│ 15:28:15 - Failed - Timeout                                    │
│ [Show More...]                                                 │
│                                                                │
│                                      [Export] [Close] [Refresh] │
└────────────────────────────────────────────────────────────────┘
```

### Time Range Selector (when clicking [Custom])
```
┌─Select Time Range──────────────────────┐
│ From: [2024-01-20 00:00] │
│ To:   [2024-01-20 23:59] │
│                                        │
│ Quick Select:                          │
│ [Last Hour] [Last 24h] [Last 7 Days]   │
│                                        │
│              [Apply] [Cancel]          │
└────────────────────────────────────────┘
```

## Notes:
1. All metrics are real-time updated
2. Graphs are interactive (hover for details)
3. Status indicators:
   - 🟢 Healthy
   - 🟡 Degraded
   - 🔴 Failed
4. Each section is collapsible
5. All data tables are sortable
6. Export options available for all data
