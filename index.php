<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - Home</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #a5b4fc 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #1f2937;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        h1 {
            color: #2563eb;
            font-size: 2.8em;
            margin-bottom: 20px;
            font-weight: 600;
        }
        p {
            color: #4b5563;
            font-size: 1.3em;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .prompts {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .prompt {
            background: #eff6ff;
            padding: 12px 24px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
            user-select: none;
            border: 1px solid #bfdbfe;
        }
        .prompt:hover {
            background: #bfdbfe;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .prompt:active {
            transform: translateY(0);
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 14px 32px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 1.2em;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .btn:active {
            transform: translateY(0);
        }
        .voice-btn {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .voice-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        .voice-btn:active {
            transform: translateY(0);
        }
        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
            h1 {
                font-size: 2em;
            }
            p {
                font-size: 1.1em;
            }
            .prompt {
                padding: 10px 20px;
                font-size: 1em;
            }
            .btn, .voice-btn {
                padding: 12px 24px;
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Your AI Assistant</h1>
        <p>Chat, generate text, or get help with tasks like writing emails or coding. Try voice input!</p>
        <div class="prompts">
            <div class="prompt" data-prompt="Write a professional email">Write a professional email</div>
            <div class="prompt" data-prompt="Summarize a text">Summarize a text</div>
            <div class="prompt" data-prompt="Help with coding">Help with coding</div>
        </div>
        <a class="btn" data-prompt="">Start Chatting</a>
        <div class="voice-btn" onclick="speakIntro()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
            Hear Introduction
        </div>
    </div>
    <script src="app.js" onerror="console.error('Failed to load app.js')"></script>
</body>
</html>
