<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin SIIKUAT</title>
    <link rel="stylesheet" href="css/face.css">
</head>
<body>
    <div class="container">
        <h1>Login Admin SIIKUAT</h1>

        <!-- Tab Buttons -->
        <div class="tab-buttons">
            <button class="tab-button active" data-tab="login">Login</button>
            <button class="tab-button" data-tab="register">Register</button>
        </div>

        <!-- Login Section -->
        <div id="loginSection">
            <div class="webcam-container">
                <video id="loginVideo" autoplay></video>
                <canvas id="loginCanvas"></canvas>
                <button id="loginCaptureButton">Capture Photo to Login</button>
            </div>
            <div id="loginStatus" class="status-message"></div>
        </div>

        <!-- Register Section -->
        <div id="registerSection" class="hidden">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" required>
            </div>

            <div class="webcam-container">
                <video id="registerVideo" autoplay></video>
                <canvas id="registerCanvas"></canvas>
                <button id="registerCaptureButton">Capture Photo for Registration</button>
            </div>
            <div id="registerStatus" class="status-message"></div>
        </div>
    </div>
    <script>
        // Store user data in localStorage
        const users = JSON.parse(localStorage.getItem('users') || '{}');

        // DOM Elements
        const tabButtons = document.querySelectorAll('.tab-button');
        const loginSection = document.getElementById('loginSection');
        const registerSection = document.getElementById('registerSection');
        const loginVideo = document.getElementById('loginVideo');
        const registerVideo = document.getElementById('registerVideo');
        const loginCanvas = document.getElementById('loginCanvas');
        const registerCanvas = document.getElementById('registerCanvas');
        const loginCaptureButton = document.getElementById('loginCaptureButton');
        const registerCaptureButton = document.getElementById('registerCaptureButton');
        const loginStatus = document.getElementById('loginStatus');
        const registerStatus = document.getElementById('registerStatus');

        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.dataset.tab;
                
                // Update active tab button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Show/hide sections
                if (tab === 'login') {
                    loginSection.classList.remove('hidden');
                    registerSection.classList.add('hidden');
                    startWebcam(loginVideo);
                } else {
                    loginSection.classList.add('hidden');
                    registerSection.classList.remove('hidden');
                    startWebcam(registerVideo);
                }
            });
        });

        // Start webcam stream
        async function startWebcam(videoElement) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                videoElement.srcObject = stream;
            } catch (err) {
                showStatus(`Error accessing webcam: ${err.message}`, false);
            }
        }

        // Initialize login webcam
        startWebcam(loginVideo);

        // Register capture button
        registerCaptureButton.addEventListener('click', () => {
            const username = document.getElementById('username').value;
            
            if (!username) {
                showStatus('Please enter a username', false, 'registerStatus');
                return;
            }

            if (users[username]) {
                showStatus('Username already exists', false, 'registerStatus');
                return;
            }

            const context = registerCanvas.getContext('2d');
            registerCanvas.width = registerVideo.videoWidth;
            registerCanvas.height = registerVideo.videoHeight;
            context.drawImage(registerVideo, 0, 0, registerCanvas.width, registerCanvas.height);

            // Save face data (in real app, this would be proper face features)
            const faceData = registerCanvas.toDataURL('image/jpeg');
            users[username] = {
                faceData: faceData,
                registeredAt: new Date().toISOString()
            };
            
            localStorage.setItem('users', JSON.stringify(users));
            showStatus('Registration successful!', true, 'registerStatus');
        });

         // Login capture button
         loginCaptureButton.addEventListener('click', () => {
            const context = loginCanvas.getContext('2d');
            loginCanvas.width = loginVideo.videoWidth;
            loginCanvas.height = loginVideo.videoHeight;
            context.drawImage(loginVideo, 0, 0, loginCanvas.width, loginCanvas.height);

            const capturedFaceData = loginCanvas.toDataURL('image/jpeg');
            
            // Simulate face matching (in real app, this would do proper face comparison)
            const userEntries = Object.entries(users);
            if (userEntries.length === 0) {
                showStatus('No registered users found', false);
                return;
            }

            // Simulate processing delay
            showStatus('Verifying...', null);
            setTimeout(() => {
                // Random success for demo (in real app, would do actual face matching)
                const isMatch = Math.random() > 0.3;

                if (isMatch) {
                    const randomUser = userEntries[Math.floor(Math.random() * userEntries.length)];
                    showStatus(`Welcome back, ${randomUser[0]}!`, true);
                    
                    // Redirect to the next page (for example, dashboard.html)
                    window.location.href = 'koneksi.php'; 
                } else {
                    showStatus('Face not recognized. Please try again.', false);
                }
            }, 1500);
        });

        // Show status message
        function showStatus(message, isSuccess) {
            loginStatus.textContent = message;
            loginStatus.className = 'status-message';
            if (isSuccess !== null) {
                loginStatus.classList.add(isSuccess ? 'success' : 'error');
            }
        }
        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (loginVideo.srcObject) {
                loginVideo.srcObject.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>
