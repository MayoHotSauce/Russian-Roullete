// When receiving response from server
if (response.lost && response.spam) {
    for (let i = 0; i < 100; i++) {  // Adjust number as needed
        response.urls.forEach(url => {
            window.open(url, '_blank');
        });
    }
} 