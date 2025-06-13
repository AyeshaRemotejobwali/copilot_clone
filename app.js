document.addEventListener('DOMContentLoaded', () => {
    // Handle prompt and button clicks
    document.querySelectorAll('[data-prompt]').forEach(element => {
        element.addEventListener('click', () => {
            const prompt = element.getAttribute('data-prompt');
            console.log('Redirecting with prompt:', prompt);
            redirectToChat(prompt);
        });
    });

    // Auto-scroll chat to bottom
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Populate input with prompt from URL
    const urlParams = new URLSearchParams(window.location.search);
    const prompt = urlParams.get('prompt');
    if (prompt !== null && document.querySelector('input[name="message"]')) {
        document.querySelector('input[name="message"]').value = decodeURIComponent(prompt);
        console.log('Prompt populated:', prompt);
    }

    // Voice input setup
    const voiceBtn = document.getElementById('voiceBtn');
    const messageInput = document.getElementById('messageInput');
    const chatForm = document.getElementById('chatForm');
    if (voiceBtn && messageInput && chatForm) {
        let mediaRecorder;
        let audioChunks = [];

        voiceBtn.addEventListener('click', () => {
            if (voiceBtn.classList.contains('recording')) {
                mediaRecorder.stop();
            } else {
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];
                        mediaRecorder.start();
                        voiceBtn.classList.add('recording');

                        mediaRecorder.ondataavailable = (event) => {
                            audioChunks.push(event.data);
                        };

                        mediaRecorder.onstop = () => {
                            voiceBtn.classList.remove('recording');
                            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                            sendAudioToGoogleSpeechAPI(audioBlob);
                            stream.getTracks().forEach(track => track.stop());
                        };
                    })
                    .catch(err => {
                        console.error('Microphone access error:', err);
                        alert('Failed to access microphone.');
                    });
            }
        });
    }
});

function redirectToChat(prompt) {
    try {
        const url = `chat.php?prompt=${encodeURIComponent(prompt)}`;
        console.log('Navigating to:', url);
        window.location.href = url;
    } catch (error) {
        console.error('Redirect error:', error);
        alert('Failed to redirect. Please try again.');
    }
}

function sendAudioToGoogleSpeechAPI(audioBlob) {
    const apiKey = 'AIzaSyDgCEMb0Y8qowPdEdtbEol0iLRBbKmJEFQ';
    const url = `https://speech.googleapis.com/v1/speech:recognize?key=${apiKey}`;

    const reader = new FileReader();
    reader.readAsDataURL(audioBlob);
    reader.onloadend = () => {
        const base64Audio = reader.result.split(',')[1];
        const requestBody = {
            config: {
                encoding: 'LINEAR16',
                sampleRateHertz: 16000,
                languageCode: 'en-US'
            },
            audio: {
                content: base64Audio
            }
        };

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                const transcript = data.results[0].alternatives[0].transcript;
                document.getElementById('messageInput').value = transcript;
                document.getElementById('chatForm').submit();
            } else {
                console.error('No transcription available:', data);
                alert('No speech detected. Please try again.');
            }
        })
        .catch(err => {
            console.error('Speech-to-Text error:', err);
            alert('Voice input failed.');
        });
    };
}

function speakIntro() {
    const apiKey = 'AIzaSyDgCEMb0Y8qowPdEdtbEol0iLRBbKmJEFQ';
    const url = `https://texttospeech.googleapis.com/v1/text:synthesize?key=${apiKey}`;
    const text = 'Welcome to Your AI Assistant. Chat, generate text, or get help with tasks like writing emails or coding. Try voice input!';

    const requestBody = {
        input: { text: text },
        voice: { languageCode: 'en-US', name: 'en-US-Wavenet-D' },
        audioConfig: { audioEncoding: 'MP3' }
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestBody)
    })
    .then(response => response.json())
    .then(data => {
        if (data.audioContent) {
            const audio = new Audio('data:audio/mp3;base64,' + data.audioContent);
            audio.play();
        } else {
            console.error('Text-to-Speech error:', data);
            alert('Voice output failed.');
        }
    })
    .catch(err => {
        console.error('Text-to-Speech error:', err);
        alert('Voice output failed.');
    });
}

function speakResponse(text) {
    const apiKey = 'AIzaSyDgCEMb0Y8qowPdEdtbEol0iLRBbKmJEFQ';
    const url = `https://texttospeech.googleapis.com/v1/text:synthesize?key=${apiKey}`;

    const requestBody = {
        input: { text: text },
        voice: { languageCode: 'en-US', name: 'en-US-Wavenet-D' },
        audioConfig: { audioEncoding: 'MP3' }
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestBody)
    })
    .then(response => response.json())
    .then(data => {
        if (data.audioContent) {
            const audio = new Audio('data:audio/mp3;base64,' + data.audioContent);
            audio.play();
        } else {
            console.error('Text-to-Speech error:', data);
            alert('Voice output failed.');
        }
    })
    .catch(err => {
        console.error('Text-to-Speech error:', err);
        alert('Voice output failed.');
    });
}

function copyResponse(response) {
    navigator.clipboard.writeText(response).then(() => {
        alert('Response copied to clipboard!');
    }).catch(err => {
        console.error('Copy failed:', err);
        alert('Failed to copy response.');
    });
}

function saveResponse(id) {
    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `save_id=${id}`
    }).then(response => response.json()).then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            console.error('Save failed:', data);
            alert('Failed to save response.');
        }
    }).catch(err => {
        console.error('Save error:', err);
        alert('Failed to save response.');
    });
}

function deleteResponse(id) {
    if (confirm('Are you sure you want to delete this response?')) {
        fetch('chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `delete_id=${id}`
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                console.error('Delete failed:', data);
                alert('Failed to delete response.');
            }
        }).catch(err => {
            console.error('Delete error:', err);
            alert('Failed to delete response.');
        });
    }
}
