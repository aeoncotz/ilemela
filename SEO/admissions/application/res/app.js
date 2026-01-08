document.getElementById('applicationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const overlay = document.getElementById('loadingOverlay');
    const loadingState = document.getElementById('loadingState');
    const successState = document.getElementById('successState');
    const percentText = document.getElementById('percentText');
    const statusHeading = document.querySelector('#loadingState h3'); // Select the heading
    const formData = new FormData(this);

    // Initial state
    overlay.style.display = 'flex';
    statusHeading.innerText = "Uploading Application";
    percentText.innerText = "0%";

    const xhr = new XMLHttpRequest();

    // 1. TRACK UPLOAD PROGRESS
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            percentText.innerText = percent + '%';
            
            // Switch message when files are fully uploaded
            if (percent === 100) {
                statusHeading.innerText = "Finalizing Submission";
            }
        }
    });

    // 2. HANDLE SERVER RESPONSE
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if(data.status === 'success') {
                        // Smooth transition to Success State
                        loadingState.style.display = 'none';
                        successState.style.display = 'block';
                        document.getElementById('successMessageText').innerText = data.message;
                        document.getElementById('refIdDisplay').innerText = data.id;
                    } else {
                        throw new Error(data.message);
                    }
                } catch (err) {
                    alert('Submission Error: ' + err.message);
                    overlay.style.display = 'none';
                }
            } else {
                alert('Connection Error. Please check your internet or file sizes.');
                overlay.style.display = 'none';
            }
        }
    };

    xhr.open('POST', 'submitter.php', true);
    xhr.send(formData);
});

// 3. COPY TO CLIPBOARD FUNCTION
function copyRefID() {
    const refId = document.getElementById('refIdDisplay').innerText;
    const icon = document.getElementById('copyIcon');
    const feedback = document.getElementById('copyFeedback');

    if (!refId) return;

    navigator.clipboard.writeText(refId).then(() => {
        // Visual feedback: Change icon to checkmark
        icon.classList.replace('bi-copy', 'bi-check2');
        icon.style.color = "#10b981"; // Success green
        feedback.style.display = 'block';

        setTimeout(() => {
            icon.classList.replace('bi-check2', 'bi-copy');
            icon.style.color = ""; 
            feedback.style.display = 'none';
        }, 2000);
    }).catch(err => {
        console.error('Copy failed:', err);
    });
}