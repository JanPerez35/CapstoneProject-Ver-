import http from 'k6/http';
import { check } from 'k6';

export const options = {
    vus: 500, 
    duration: '1s', 
    insecureSkipTLSVerify: true,
};

export default function () {
    const res = http.post('https://maikine.uprm.edu/test-concurrency');

    console.log(`STATUS: ${res.status}`);
    console.log(`BODY: ${res.body}`);

    check(res, {
        'status 200': (r) => r.status === 200,
    });    
}