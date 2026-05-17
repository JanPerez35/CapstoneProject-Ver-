/**
 * MAIKINE Email Performance Test - Low Load
 *
 * Sends 100 email requests to the real MAIKINE server.
 * The endpoint sends all emails to the selected testing email.
 *
 * Metrics measured:
 * - total requests
 * - successful requests
 * - failed requests
 * - total execution time
 * - throughput in emails per minute
 * - average latency per request
 *
 * Run with:
 * node resources/js/email_testing/test-emails-100.js
 */

const TOTAL_REQUESTS = 100;
const CONCURRENCY = 10;
const TEST_EMAIL = 'maikinenoreply@gmail.com';

const URL = `https://maikine.uprm.edu/test-email/performance?email=${encodeURIComponent(TEST_EMAIL)}`;

let completedRequests = 0;
let failedRequests = 0;
const latencies = [];

/**
 * Sends one HTTP request to the testing endpoint
 * and records its latency.
 */
async function sendRequest(requestNumber) {
    const startTime = performance.now();

    try {
        const response = await fetch(URL);
        const endTime = performance.now();

        const latencySeconds = (endTime - startTime) / 1000;
        latencies.push(latencySeconds);

        if (!response.ok) {
            failedRequests++;
            console.log(`Request ${requestNumber} failed with status ${response.status}`);
            return;
        }

        completedRequests++;
    } catch (error) {
        failedRequests++;
        console.log(`Request ${requestNumber} failed: ${error.message}`);
    }
}

/**
 * Runs requests with limited concurrency.
 */
async function runTest() {
    const testStartTime = performance.now();
    let currentRequest = 1;

    async function worker() {
        while (currentRequest <= TOTAL_REQUESTS) {
            const requestNumber = currentRequest++;
            await sendRequest(requestNumber);
        }
    }

    const workers = [];

    for (let i = 0; i < CONCURRENCY; i++) {
        workers.push(worker());
    }

    await Promise.all(workers);

    const testEndTime = performance.now();
    const totalSeconds = (testEndTime - testStartTime) / 1000;

    const averageLatency = latencies.length
        ? latencies.reduce((sum, value) => sum + value, 0) / latencies.length
        : 0;

    const throughput = (completedRequests / totalSeconds) * 60;

    console.log('\n===== MAIKINE EMAIL PERFORMANCE TEST - 100 EMAILS =====');
    console.log(`Target Email: ${TEST_EMAIL}`);
    console.log(`Total Requests: ${TOTAL_REQUESTS}`);
    console.log(`Concurrency: ${CONCURRENCY}`);
    console.log(`Completed: ${completedRequests}`);
    console.log(`Failed: ${failedRequests}`);
    console.log(`Total Time: ${totalSeconds.toFixed(2)} s`);
    console.log(`Throughput: ${throughput.toFixed(2)} emails/min`);
    console.log(`Average Latency: ${averageLatency.toFixed(3)} s`);
}

runTest();
