<!---------   

=== Theme Designed By: Mark Ballerda
=== Contact Whatsapp: +639205648851
  -----------> 

<?php include 'header.php'; ?>
<style>
.feedback-container {
    max-width: 500px;
    margin: 20px auto;
    padding: 15px;
}

.feedback-header {
    background: #317fb5;
    color: white;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
}

.remaining-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 3px 8px;
    border-radius: 3px;
}

.form-box {
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 20px;
}

.form-title {
    color: #317fb5;
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    color: #555;
    font-size: 13px;
    margin-bottom: 5px;
}

.form-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.form-input:focus {
    outline: none;
    border-color: #317fb5;
}

.type-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.type-option {
    background: none;
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    color: #555;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.type-option:before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(49, 127, 181, 0.1);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.4s ease, height 0.4s ease;
}

.type-option:hover {
    border-color: #317fb5;
    color: #317fb5;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(49, 127, 181, 0.1);
}

.type-option:hover:before {
    width: 150%;
    height: 150%;
}

.type-option.active {
    background: #317fb5;
    border-color: #317fb5;
    color: white;
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(49, 127, 181, 0.2);
}

.type-option:active {
    transform: translateY(1px);
}

.message-box {
    min-height: 100px;
    resize: vertical;
}

.submit-button {
    width: 100%;
    background: #317fb5;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    z-index: 1;
}

.submit-button:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        120deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    transition: all 0.5s ease;
    z-index: -1;
}

.submit-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(49, 127, 181, 0.2);
    opacity: 1;
}

.submit-button:hover:before {
    left: 100%;
}

.submit-button:active {
    transform: translateY(1px);
    box-shadow: 0 2px 4px rgba(49, 127, 181, 0.2);
}

@keyframes buttonPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(49, 127, 181, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(49, 127, 181, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(49, 127, 181, 0);
    }
}

.type-option.active {
    animation: buttonPulse 1.5s infinite;
}

.alert {
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
}

.alert-success {
    background: #e3f2fd;
    border: 1px solid #317fb5;
    color: #317fb5;
}

.alert-error {
    background: #fee;
    border: 1px solid #f55;
    color: #f55;
}

@media (max-width: 480px) {
    .feedback-container {
        padding: 10px;
    }
    .form-box {
        padding: 15px;
    }
}
</style>

<div class="feedback-container">
    <div class="feedback-header">
        <span>Daily Feedback Limit</span>
        <span class="remaining-count"><?php echo $remainingSubmissions; ?> remaining</span>
    </div>

    <div class="form-box">
        <h3 class="form-title">Share Your Feedback</h3>

        <?php if (isset($errorMessage)): ?>
            <div class="alert <?php echo strpos(strtolower($errorMessage), 'success') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form action="admin/feedback" method="post">
            <div class="form-group">
                <label class="form-label" for="feedbackTitle">Title</label>
                <input type="text" 
                       class="form-input" 
                       name="title" 
                       id="feedbackTitle" 
                       placeholder="Brief title"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Type</label>
                <div class="type-grid">
                    <input type="radio" name="type" value="bug" id="typeBug" hidden checked>
                    <label for="typeBug" class="type-option" onclick="setActive(this)">Bug Report</label>
                    
                    <input type="radio" name="type" value="suggestion" id="typeSuggestion" hidden>
                    <label for="typeSuggestion" class="type-option" onclick="setActive(this)">Suggestion</label>
                    
                    <input type="radio" name="type" value="compliment" id="typeCompliment" hidden>
                    <label for="typeCompliment" class="type-option" onclick="setActive(this)">Compliment</label>
                    
                    <input type="radio" name="type" value="other" id="typeOther" hidden>
                    <label for="typeOther" class="type-option" onclick="setActive(this)">Other</label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="feedbackMessage">Message</label>
                <textarea class="form-input message-box" 
                          id="feedbackMessage" 
                          name="message" 
                          placeholder="Your message..."
                          required></textarea>
            </div>

            <button type="submit" class="submit-button">
                Send Feedback
            </button>
        </form>
    </div>
</div>

<script>
function setActive(element) {
    document.querySelectorAll('.type-option').forEach(el => {
        el.classList.remove('active');
        el.style.animation = 'none';
    });
    
    element.classList.add('active');
    element.style.animation = 'none';
    element.offsetHeight;
    element.style.animation = null;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('label[for="typeBug"]').classList.add('active');
    
    const submitButton = document.querySelector('.submit-button');
    submitButton.addEventListener('click', function(e) {
        let ripple = document.createElement('span');
        ripple.style.cssText = `
            position: absolute;
            background: rgba(255, 255, 255, 0.7);
            transform: translate(-50%, -50%);
            pointer-events: none;
            border-radius: 50%;
            animation: ripple 0.6s linear;
        `;
        
        ripple.style.left = e.offsetX + 'px';
        ripple.style.top = e.offsetY + 'px';
        
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });
});

const style = document.createElement('style');
style.textContent = `
@keyframes ripple {
    0% {
        width: 0;
        height: 0;
        opacity: 0.5;
    }
    100% {
        width: 500px;
        height: 500px;
        opacity: 0;
    }
}`;
document.head.appendChild(style);
</script>

<?php include 'footer.php'; ?>