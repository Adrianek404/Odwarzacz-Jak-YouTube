function startDictation() {

    if (window.hasOwnProperty('webkitSpeechRecognition')) {
        var recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = "pl-PL";
        recognition.start();
        recognition.onresult = function (e) {
            document.getElementById('search').value
                = e.results[0][0].transcript;
            recognition.stop();
            setTimeout(function () {
                document.getElementById('search-btn').click();
            }, 2000)
        };
        recognition.onerror = function (e) {
            recognition.stop();
        }
    }
}

function hideChannel() {
    if (window.getComputedStyle(document.getElementById('user-content')).display === "flex") {
        document.getElementById('user-content').style.display = "none";
    }
}

function Showchannel() {
    if (window.getComputedStyle(document.getElementById('user-content')).display === "none") {
        document.getElementById('user-content').style.display = "flex";
    } else {
        document.getElementById('user-content').style.display = "none";
    }
}
