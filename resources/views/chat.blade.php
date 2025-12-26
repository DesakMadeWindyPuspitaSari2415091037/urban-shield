<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>UrbanShield Chatbot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .logo-header {
            position: fixed;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            z-index: 1000;
        }

        .logo-header img {
            height: 50px;
            margin-right: 10px;
        }

        .logo-header span {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .chat-container {
            width: 100%;
            max-width: 600px;
            margin: 100px auto 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 75vh;
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .message {
            margin: 10px 0;
            padding: 12px 16px;
            border-radius: 20px;
            max-width: 80%;
            line-height: 1.4;
            white-space: pre-wrap;
        }

        .user {
            background: #d1e7dd;
            align-self: flex-end;
        }

        .bot {
            background: #e0e0e0;
            align-self: flex-start;
        }

        .input-box {
            display: flex;
            padding: 15px;
            border-top: 1px solid #ccc;
        }

        .input-box input {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 16px;
        }

        .input-box button {
            margin-left: 10px;
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            background: #4a4a4a;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        .input-box button:hover {
            background: #333;
        }
    </style>
</head>
<body>


    <!-- Kontainer Chat -->
    <div class="chat-container">
        <div class="chat-box" id="chatBox">
            <!-- Pesan akan muncul di sini -->
        </div>

        <form id="chatForm" class="input-box">
            @csrf
            <input type="text" name="message" placeholder="Tanya sesuatu..." autocomplete="off">
            <button type="submit">Kirim</button>
        </form>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');

        // Fungsi menambahkan pesan ke chat box
        function addMessage(text, type) {
            const div = document.createElement('div');
            div.classList.add('message', type);
            div.innerText = text;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Pesan pembuka otomatis saat halaman dimuat
        window.onload = function() {
            const opening = "Hi there! 👋 I'm UrbanShield, your personal safety assistant. I'm here to help you with disaster preparedness, emergency procedures, and first aid tips. What do you need to know?";
            addMessage(opening, 'bot');
        };

        // Kirim pesan ke backend saat form disubmit
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            let message = this.message.value.trim();
            if (!message) return;

            addMessage(message, 'user');
            this.message.value = '';

            let res = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message })
            });

            let data = await res.json();
            addMessage(data.answer, 'bot');
        });
    </script>

</body>
</html>
