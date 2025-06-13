<?php
session_start();
require_once 'db.php';

// Generate a user ID if not already set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = bin2hex(random_bytes(16));
}

// Handle save response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_id'])) {
    $stmt = $pdo->prepare("UPDATE chat_history SET is_saved = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['save_id'], $_SESSION['user_id']]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Handle delete response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM chat_history WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['delete_id'], $_SESSION['user_id']]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Save user message to database
        $stmt = $pdo->prepare("INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)");
        $response = callHuggingFaceAPI($message);
        $stmt->execute([$_SESSION['user_id'], $message, $response]);

        // Save response if requested
        if (isset($_POST['save_response'])) {
            $stmt = $pdo->prepare("UPDATE chat_history SET is_saved = 1 WHERE user_id = ? AND message = ?");
            $stmt->execute([$_SESSION['user_id'], $message]);
        }

        // Redirect to refresh chat
        header("Location: chat.php?prompt=");
        exit;
    }
}

// Function to call Hugging Face API
function callHuggingFaceAPI($input) {
    $apiKey = 'YOUR_HUGGING_FACE_API_KEY'; // Replace with your Hugging Face API key
    $url = 'https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill';
    $data = json_encode(['inputs' => $input]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Hugging Face API Error: ' . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['generated_text'] ?? 'Sorry, I could not process that request.';
}

// Fetch chat history
$stmt = $pdo->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - Chat</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff ė0%, #a5b4fc 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .chat-container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
            display: flex;
            height: 80vh;
        }
        .sidebar {
            width: 30%;
            background: #f9fafb;
            padding: 20px;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
        }
        .chat-area {
            width: 70%;
            display: flex;
            flex-direction: column;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f9fafb;
        }
        .message {
            margin: 12px 0;
            padding: 12px 18px;
            border-radius: 12px;
            max-width: 75%;
            line-height: 1.5;
            position: relative;
        }
        .user-message {
            background: #2563eb;
            color: white;
            margin-left: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .ai-message {
            background: #eff6ff;
            color: #1f2937;
            margin-right: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .ai-message .speak-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            color: #2563eb;
        }
        .input-area {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }
        .input-area form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .input-area input[type="text"] {
            flex: 1;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1em;
            outline: none;
            transition: border-color 0.3s;
        }
        .input-area input[type="text"]:focus {
            border-color: #2563eb;
        }
        .input-area button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .input-area button:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }
        .input-area button:active {
            transform: translateY(0);
        }
        .voice-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        .voice-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        .voice-btn:active {
            transform: translateY(0);
        }
        .voice-btn.recording {
            background: #dc2626;
        }
        .input-area label {
            font-size: 0.9em;
            color: #4b5563;
        }
        .history-item {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.95em;
            color: #1f2937;
        }
        .history-item:hover {
            background: #eff6ff;
        }
        .saved {
            font-weight: 600;
            color: #2563eb;
        }
        .action-btn {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
            font-size: 0.9em;
            margin-left: 8px;
            transition: color 0.3s;
        }
        .action-btn:hover {
            color: #1e40af;
        }
        @media (max-width: 600px) {
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
            .chat-area {
                width: 100%;
            }
            .chat-messages {
                padding: 15px;
            }
            .input-area {
                padding: 15px;
            }
            .input-area form {
                flex-direction: column;
                gap: 10px;
            }
            .input-area input[type="text"], .input-area button, .voice-btn {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="sidebar">
            <h3>Chat History</h3>
            <?php foreach ($history as $item): ?>
                <div class="history-item <?php echo $item['is_saved'] ? 'saved' : ''; ?>">
                    <?php echo htmlspecialchars(substr($item['message'], 0, 50)) . '...'; ?>
                    <?php if ($item['is_saved']): ?>
                        <button class="action-btn" onclick="copyResponse('<?php echo htmlspecialchars($item['response']); ?>')">Copy</button>
                        <button class="action-btn" onclick="deleteResponse(<?php echo $item['id']; ?>)">Delete</button>
                    <?php else: ?>
                        <button class="action-btn" onclick="saveResponse(<?php echo $item['id']; ?>)">Save</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chat-area">
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($history as $item): ?>
                    <div class="message user-message"><?php echo htmlspecialchars($item['message']); ?></div>
                    <div class="message ai-message">
                        <?php echo htmlspecialchars($item['response']); ?>
                        <button class="speak-btn" onclick="speakResponse('<?php echo htmlspecialchars($item['response']); ?>')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="input-area">
                <form method="POST" id="chatForm">
                    <input type="text" name="message" id="messageInput" placeholder="Type or speak your message..." required>
                    <button type="submit">Send</button>
                    <button type="button" class="voice-btn" id="voiceBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <line x1="12" y1="19" x2="12" y2="23"></line>
                            <line x1="8" y1="23" x2="16" y2="23"></line>
                        </svg>
                    </button>
                    <label><input type="checkbox" name="save_response"> Save Response</label>
                </form>
            </div>
        </div>
    </div>
    <script src="app.js" onerror="console.error('Failed to load app.js')"></script>
</body>
</html>
