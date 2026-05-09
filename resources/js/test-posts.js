import http from 'k6/http';
import { check } from 'k6';

export const options = {
    vus: 208, // Número de usuarios virtuales concurrentes
    duration: '1s', // Duración total de la prueba
    insecureSkipTLSVerify: true,
};

export default function () {
    const res = http.post('https://maikine.test/test-concurrency');

    console.log(`STATUS: ${res.status}`);

    check(res, {
        'status 200': (r) => r.status === 200,
    });    
}