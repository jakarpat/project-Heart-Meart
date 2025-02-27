<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matching System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .card h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Matching System</h1>
        <p>Choose your matching method below</p>
    </header>

    <div class="container">
        <!-- Matching Based on Profiles -->
        <div class="card">
            <h2>1. Matching Based on Profiles</h2>
            <form id="profile-matching">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" placeholder="Enter your age">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Enter your location">
                </div>
                <div class="form-group">
                    <button type="button" onclick="matchProfiles()">Find Matches</button>
                </div>
            </form>
        </div>

        <!-- Algorithmic Matching -->
        <div class="card">
            <h2>2. Algorithmic Matching</h2>
            <form id="algorithm-matching">
                <div class="form-group">
                    <label for="question1">Do you enjoy outdoor activities?</label>
                    <select id="question1" name="question1">
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="question2">What is your ideal type of partner?</label>
                    <input type="text" id="question2" name="question2" placeholder="Describe your ideal partner">
                </div>
                <div class="form-group">
                    <button type="button" onclick="algorithmicMatch()">Get Suggestions</button>
                </div>
            </form>
        </div>

        <!-- Proximity Matching -->
        <div class="card">
            <h2>3. Proximity Matching</h2>
            <form id="proximity-matching">
                <div class="form-group">
                    <label for="distance">Select Distance (in km)</label>
                    <input type="range" id="distance" name="distance" min="1" max="100" step="1" oninput="updateDistance(this.value)">
                    <p>Selected Distance: <span id="distanceValue">50</span> km</p>
                </div>
                <div class="form-group">
                    <button type="button" onclick="proximityMatch()">Find Nearby Matches</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function matchProfiles() {
            alert('Finding matches based on profiles!');
        }

        function algorithmicMatch() {
            alert('Finding matches based on algorithm!');
        }

        function proximityMatch() {
            alert('Finding matches based on proximity!');
        }

        function updateDistance(value) {
            document.getElementById('distanceValue').innerText = value;
        }
    </script>
</body>
</html>