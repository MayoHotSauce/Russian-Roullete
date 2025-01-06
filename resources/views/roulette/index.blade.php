<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Russian Roulette</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes recoil {
            0% { transform: rotate(0deg); }
            20% { transform: rotate(-20deg); }
            100% { transform: rotate(0deg); }
        }
        @keyframes reload {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        @keyframes spinCylinder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .cylinder-spin {
            animation: spin 0.5s linear infinite;
        }
        .revolver {
            transition: transform 0.3s;
            width: 300px;
            height: 200px;
        }
        .revolver:hover {
            transform: scale(1.05);
        }
        .shoot {
            animation: recoil 0.3s ease-in-out;
        }
        .reload {
            animation: reload 1s ease-in-out;
        }
        .muzzle-flash {
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }
        .cylinder-container {
            position: absolute;
            right: -80px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            opacity: 1;
        }
        .cylinder {
            width: 100%;
            height: 100%;
            border: 4px solid #4a4a4a;
            border-radius: 50%;
            position: relative;
            background: #333;
        }
        .chamber {
            width: 12px;
            height: 12px;
            background: #1a1a1a;
            border-radius: 50%;
            position: absolute;
        }
        .bullet {
            width: 10px;
            height: 10px;
            background: #ffd700;
            border-radius: 50%;
            position: absolute;
            top: 1px;
            left: 1px;
            opacity: 1;
            transition: opacity 0.3s;
        }
        .blinking {
            animation: blink 0.5s ease-in-out infinite;
        }
        .spinning {
            animation: spinCylinder 0.2s linear infinite;
        }
        .hide-bullets .bullet {
            opacity: 0;
        }
        .chamber:nth-child(1) { top: 5px; left: 50%; transform: translateX(-50%); }
        .chamber:nth-child(2) { top: 25%; right: 5px; }
        .chamber:nth-child(3) { bottom: 25%; right: 5px; }
        .chamber:nth-child(4) { bottom: 5px; left: 50%; transform: translateX(-50%); }
        .chamber:nth-child(5) { bottom: 25%; left: 5px; }
        .chamber:nth-child(6) { top: 25%; left: 5px; }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-600 mb-8">Russian Roulette</h1>
        
        <div class="relative w-96 h-64 mx-auto mb-8">
            <img src="{{ asset('images/revolver.png') }}" 
                 alt="Revolver" 
                 class="revolver w-full h-full object-contain" 
                 id="revolver">
            
            <!-- Add the cylinder structure here -->
            <div class="cylinder-container" id="cylinderContainer">
                <div class="cylinder" id="cylinder">
                    <div class="chamber"><div class="bullet" id="bullet"></div></div>
                    <div class="chamber"></div>
                    <div class="chamber"></div>
                    <div class="chamber"></div>
                    <div class="chamber"></div>
                    <div class="chamber"></div>
                </div>
            </div>
            
            <!-- Muzzle flash -->
            <img src="{{ asset('images/muzzle-flash.png') }}"
                 class="muzzle-flash w-24 absolute left-full top-1/2 transform -translate-y-1/2 hidden"
                 id="muzzleFlash">
        </div>

        <div class="space-y-4">
            <button id="playButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg text-xl transform transition hover:scale-105">
                Pull the Trigger
            </button>

            <button id="reloadButton" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-8 rounded-lg text-xl transform transition hover:scale-105 ml-4">
                Reload
            </button>
        </div>

        <div id="result" class="mt-8 text-2xl text-white hidden"></div>
    </div>

    <div id="overlay" class="fixed inset-0 bg-black opacity-0 pointer-events-none transition-opacity duration-1000"></div>

    <audio id="gunshot" src="https://www.soundjay.com/mechanical/sounds/gun-shot-1.mp3" preload="auto"></audio>
    <audio id="empty-click" src="https://www.soundjay.com/mechanical/sounds/gun-click-1.mp3" preload="auto"></audio>
    <audio id="reload-sound" src="https://www.soundjay.com/mechanical/sounds/gun-reload-1.mp3" preload="auto"></audio>

    <script>
        const revolver = document.getElementById('revolver');
        const cylinder = document.getElementById('cylinder');
        const cylinderContainer = document.getElementById('cylinderContainer');
        const playButton = document.getElementById('playButton');
        const reloadButton = document.getElementById('reloadButton');
        const gunshot = document.getElementById('gunshot');
        const emptyClick = document.getElementById('empty-click');
        const reloadSound = document.getElementById('reload-sound');
        let bullets = 6;

        reloadButton.addEventListener('click', function() {
            // First show all bullets
            cylinder.classList.remove('hide-bullets');
            
            // Start spinning after a brief moment
            setTimeout(() => {
                // Start spinning cylinder
                cylinder.classList.add('spinning');
                
                // Hide bullets during mid-spin and keep them hidden
                setTimeout(() => {
                    cylinder.classList.add('hide-bullets');
                }, 500); // Hide bullets after 0.5s of spinning
                
                // After 1.5 seconds total, stop spinning
                setTimeout(() => {
                    cylinder.classList.remove('spinning');
                    
                    // Random position for bullet (1-6)
                    const randomChamber = Math.floor(Math.random() * 6);
                    const chambers = document.querySelectorAll('.chamber');
                    const bullet = document.getElementById('bullet');
                    
                    // Move bullet to random chamber
                    chambers.forEach((chamber, index) => {
                        if (index === randomChamber) {
                            chamber.appendChild(bullet);
                        }
                    });
                    
                    // Keep bullets hidden after spin
                    bullets = 6;
                }, 1500);
            }, 200);
        });

        playButton.addEventListener('click', async function() {
            if (bullets <= 0) {
                alert('Please reload the gun!');
                return;
            }

            const button = this;
            const result = document.getElementById('result');
            const overlay = document.getElementById('overlay');

            button.disabled = true;
            result.classList.add('hidden');
            bullets--;

            try {
                const response = await fetch('/play', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                setTimeout(() => {
                    // Show the bullet briefly when firing
                    cylinder.classList.remove('hide-bullets');
                    
                    if (data.lost) {
                        result.innerHTML = "BANG! You lost! 💀";
                        result.classList.remove('hidden');
                        overlay.style.opacity = '1';
                    } else {
                        result.innerHTML = "Click! You survived! 🍀";
                        result.classList.remove('hidden');
                        button.disabled = false;
                    }
                    
                    // Hide bullets again after showing result
                    setTimeout(() => {
                        cylinder.classList.add('hide-bullets');
                    }, 500);
                }, 500);

            } catch (error) {
                console.error('Error:', error);
                result.innerHTML = "Something went wrong!";
                result.classList.remove('hidden');
                button.disabled = false;
            }
        });
    </script>
</body>
</html> 