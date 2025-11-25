// This Azure Function runs on a schedule to trigger the Civic Utopia News Agent
const https = require('https');

module.exports = async function (context, myTimer) {
    const timeStamp = new Date().toISOString();

    if (myTimer.isPastDue) {
        context.log('Civic Utopia News Trigger is running late!');
    }

    context.log('Azure Scheduler triggering News Agent...', timeStamp);

    // The coordinates for the default "Community View" (e.g., Kingston)
    // In a full production app, this might loop through all active user zones.
    const postData = JSON.stringify({
        'lat': 18.0179,
        'lon': -76.8099,
        'source': 'azure_function_timer'
    });

    const options = {
        hostname: 'civic-utopia.test', // Your production domain goes here
        port: 443,
        path: '/api/webhooks/azure/trigger-news',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': postData.length,
            'X-Azure-Function-Key': process.env.APP_KEY // Secure handshake
        }
    };

    const req = https.request(options, (res) => {
        context.log(`StatusCode: ${res.statusCode}`);
        res.on('data', (d) => {
            process.stdout.write(d);
        });
    });

    req.on('error', (error) => {
        context.log.error(error);
    });

    req.write(postData);
    req.end();

    context.log('News Agent Trigger completed.');
};
