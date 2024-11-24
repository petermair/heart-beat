/**
 * LPP Encoder/Decoder for HeartBeat messages
 * Format:
 * - Channel 1: flow_type (1 byte signed, type 0x00) - values 1-9
 * - Channel 2: counter (4 bytes unsigned, type 0xfe)
 * - Channel 3: timestamp (4 bytes unsigned, type 0xfe)
 */

// Constants
const DIGITAL_INPUT_TYPE = 0x00;
const UNSIGNED_4B_TYPE = 0xfe;

/**
 * Encode JSON to LPP binary format
 * @param {Object} json - Format: { flow_type: number, counter: number, timestamp: number }
 * @returns {Buffer} - LPP encoded buffer
 */
function encodeLpp(json) {
    // Allocate buffer: 3 channels * (1 byte channel + 1 byte type + max 4 bytes data)
    const buffer = Buffer.alloc(3 * 6);
    let offset = 0;

    // Channel 1: flow_type (1 byte)
    buffer.writeUInt8(1, offset++);                    // Channel
    buffer.writeUInt8(DIGITAL_INPUT_TYPE, offset++);   // Type
    buffer.writeInt8(json.flow_type, offset++);        // Value

    // Channel 2: counter (4 bytes)
    buffer.writeUInt8(2, offset++);                    // Channel
    buffer.writeUInt8(UNSIGNED_4B_TYPE, offset++);     // Type
    buffer.writeUInt32BE(json.counter, offset);        // Value
    offset += 4;

    // Channel 3: timestamp (4 bytes)
    buffer.writeUInt8(3, offset++);                    // Channel
    buffer.writeUInt8(UNSIGNED_4B_TYPE, offset++);     // Type
    buffer.writeUInt32BE(json.timestamp, offset);      // Value
    offset += 4;

    return buffer.slice(0, offset);
}

/**
 * Decode LPP binary to JSON format
 * @param {Buffer} buffer - LPP encoded buffer
 * @returns {Object} - Decoded JSON with ThingsBoard field names
 */
function decodeLpp(buffer) {
    const result = {};
    let offset = 0;

    while (offset < buffer.length) {
        const channel = buffer.readUInt8(offset++);
        const type = buffer.readUInt8(offset++);

        switch (type) {
            case DIGITAL_INPUT_TYPE:
                // 1 byte signed
                result[`f001digitalinput${channel}`] = buffer.readInt8(offset);
                offset += 1;
                break;

            case UNSIGNED_4B_TYPE:
                // 4 bytes unsigned
                result[`f001unsigned4b${channel}`] = buffer.readUInt32BE(offset);
                offset += 4;
                break;

            default:
                throw new Error(`Unknown type: ${type}`);
        }
    }

    return result;
}

/**
 * Example usage:
 * 
 * // Encoding
 * const json = {
 *     flow_type: 1,  // Flow 1
 *     counter: 12345,
 *     timestamp: Math.floor(Date.now() / 1000)
 * };
 * const lppBuffer = encodeLpp(json);
 * const base64 = lppBuffer.toString('base64');
 * 
 * // Decoding
 * const decoded = decodeLpp(Buffer.from(base64, 'base64'));
 * console.log(decoded);
 * // Output:
 * // {
 * //     f001digitalinput1: 1,
 * //     f001unsigned4b2: 12345,
 * //     f001unsigned4b3: 1708123456
 * // }
 */

// Node.js exports
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        encodeLpp,
        decodeLpp
    };
}
