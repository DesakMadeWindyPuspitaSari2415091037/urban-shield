<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>UrbanShield Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-wrapper {
            width: 100%;
            max-width: 960px;
            height: 90vh;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            font-size: 22px;
            font-weight: bold;
        }
        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .chat-message {
            margin-bottom: 16px;
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 16px;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .chat-message.bot {
            background-color: #e0f0ff;
            color: #007bff;
            align-self: flex-start;
        }
        .chat-message.user {
            background-color: #d1ffd1;
            color: #333;
            align-self: flex-end;
        }
        .chat-footer {
            display: flex;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        input[type="text"] {
            flex: 1;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 14px 24px;
            margin-left: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-wrapper">
        <div class="chat-header">UrbanShield 🛡️</div>
        <div class="chat-body" id="chatBody">
            <div class="chat-message bot">
                Hi there! 👋 Saya UrbanShield, siap bantu kamu soal kebencanaan dan keselamatan. Apa yang ingin kamu tahu?
            </div>
        </div>
        <div class="chat-footer">
            <input type="text" id="messageInput" placeholder="Tulis pertanyaan kamu..." onkeydown="if(event.key === 'Enter') sendMessage()" />
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>

    <script>
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message) return;

            appendMessage('user', message);
            input.value = '';

            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'chat-message bot';
            typingIndicator.id = 'typing-indicator';
            typingIndicator.textContent = 'UrbanShield sedang mengetik...';
            document.getElementById('chatBody').appendChild(typingIndicator);
            scrollToBottom();

            fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('typing-indicator')?.remove();
                appendMessage('bot', data.answer);
            })
            .catch(err => {
                document.getElementById('typing-indicator')?.remove();
                appendMessage('bot', 'Maaf, terjadi kesalahan.');
                console.error(err);
            });
        }

        function appendMessage(sender, text) {
            const chatBody = document.getElementById('chatBody');
            const div = document.createElement('div');
            div.className = 'chat-message ' + sender;
            div.textContent = text;
            chatBody.appendChild(div);
            scrollToBottom();
        }

        function scrollToBottom() {
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    </script>
</body>
</html>
