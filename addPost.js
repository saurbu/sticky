let selectedBgImage = null;
let chosenBgColor = null;    
let chosenTextColor = "#111111";  

document.addEventListener("DOMContentLoaded", function() {
    const textareaElement = document.getElementById("newPostContent");
    const counterDisplay = document.getElementById("charCounter");
    
    // Character Limit Monitor
    if (textareaElement && counterDisplay) {
        textareaElement.addEventListener("input", function() {
            let count = this.value.length;
            counterDisplay.textContent = `${count} / 300`;
        });
        // Force static black text for the input box
        textareaElement.style.color = "#111111";
    }
    
    // Inject status placeholder
    const createPostCard = document.querySelector(".create-post");
    if (createPostCard && !document.getElementById("creationStatusIndicator")) {
        const statusBanner = document.createElement("div");
        statusBanner.id = "creationStatusIndicator";
        statusBanner.style.cssText = "font-size: 12px; color: #707e08; font-weight: 600; padding: 4px 25px; background: rgba(112, 126, 8, 0.08); display: none; text-align: left; border-top: 1px dashed rgba(0,0,0,0.05); font-family: 'poppins', sans-serif;";
        createPostCard.insertBefore(statusBanner, document.querySelector(".create-post-links"));
    }

    const templateColorPicker = document.getElementById("templateColorPicker");
    const textColorPicker = document.getElementById("textColorPicker");

    // Dynamic Color Templates
    if (templateColorPicker) {
        templateColorPicker.addEventListener("input", function(e) {
            selectedBgImage = null;
            const fileInput = document.getElementById("bgImageUpload");
            if (fileInput) fileInput.value = "";
            chosenBgColor = e.target.value;
            updateCreationStatus(`✓ Template Color: ${chosenBgColor.toUpperCase()}`);
        });
    }

    // Live text color manipulation (Static textarea, UI labels only)
    if (textColorPicker) {
        textColorPicker.addEventListener("input", function(e) {
            chosenTextColor = e.target.value;
            
            // Textarea stays static black
            if (textareaElement) {
                textareaElement.style.color = "#111111";
            }
            
            // Update UI indicators/labels only
            if (counterDisplay) {
                counterDisplay.style.color = chosenTextColor;
            }
        });
    }

    if (typeof activeFeedMode === 'undefined') {
        window.activeFeedMode = 'home';
    }

    loadDatabaseFeed();
});

function updateCreationStatus(message) {
    const indicator = document.getElementById("creationStatusIndicator");
    if (indicator) {
        if (message) {
            indicator.textContent = message;
            indicator.style.display = "block";
        } else {
            indicator.style.display = "none";
        }
    }
}

function triggerBackgroundUpload() {
    document.getElementById("bgImageUpload").click();
}

function handleBackgroundSelect(event) {
    const file = event.target.files[0];
    if (file) {
        if (!file.type.match('image.*')) {
            alert("Invalid file type! Use JPG, JPEG, PNG, or GIF.");
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            chosenBgColor = null;
            selectedBgImage = e.target.result;
            updateCreationStatus("✓ Custom Background Image Added");
        };
        reader.readAsDataURL(file);
    }
}

function togglePostMenu(id, event) {
    event.stopPropagation();
    const menu = document.getElementById(`menu-${id}`);
    document.querySelectorAll('.post-options-dropdown').forEach(d => {
        if (d.id !== `menu-${id}`) d.classList.remove('active');
    });
    if (menu) menu.classList.toggle('active');
}

window.addEventListener('click', function() {
    document.querySelectorAll('.post-options-dropdown').forEach(dropdown => {
        dropdown.classList.remove('active');
    });
});

function toggleLikeNote(postId, element) {
    const heartSvg = element.querySelector('.heart-icon');
    const counterSpan = element.querySelector('.like-counter-display');
    let currentCount = parseInt(counterSpan.textContent, 10);
    if (isNaN(currentCount)) currentCount = 0;
    
    let actionType = 'increment';

    if (heartSvg.classList.contains('liked-active')) {
        heartSvg.classList.remove('liked-active');
        counterSpan.classList.remove('liked-text');
        counterSpan.textContent = Math.max(0, currentCount - 1);
        actionType = 'decrement';
    } else {
        heartSvg.classList.add('liked-active');
        counterSpan.classList.add('liked-text');
        counterSpan.textContent = currentCount + 1;
        actionType = 'increment';
    }

    fetch("posts_api.php?action=like", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: postId, type: actionType })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status !== 'success') {
            console.error("Database like syncing error.");
        }
    })
    .catch(err => console.error("Communication failure:", err));
}

function loadDatabaseFeed() {
    fetch(`posts_api.php?action=fetch&mode=${window.activeFeedMode}`)
    .then(res => res.json())
    .then(posts => {
        const container = document.getElementById("post-container");
        if (!container) return;
        
        container.innerHTML = "";
        
        if(posts.length === 0) {
            container.innerHTML = `<div style="padding:40px; text-align:center; font-weight:600; color:#555; font-family:'poppins', sans-serif; width:100%; grid-column: 1 / -1;">No matching sticky notes available in this feed section.</div>`;
            return;
        }

        posts.forEach(post => {
            let customInlineStyle = "";
            if (post.bg_image) {
                customInlineStyle = `style="background-image: url('${post.bg_image}'); background-size: cover; background-position: center;"`;
            } else if (post.bg_color) {
                customInlineStyle = `style="background-image: none !important; background-color: ${post.bg_color} !important;"`;
            }

            let optionsDropdownHTML = "";
            if(post.username === currentSessionUser) {
                optionsDropdownHTML = `
                <div class="post-options-container">
                    <div class="post-options" onclick="togglePostMenu(${post.id}, event)">•••</div>
                    <div class="post-options-dropdown" id="menu-${post.id}">
                        <button onclick="deletePost(${post.id})" class="dropdown-delete-action">Delete Note</button>
                    </div>
                </div>`;
            }

            const heartActiveClass = post.user_has_liked ? "liked-active" : "";
            const counterActiveClass = post.user_has_liked ? "liked-text" : "";

            let html_temp = `
            <div class="notes-container" id="postKey${post.id}"> 
                <div class="notes1 ${post.template_class}" ${customInlineStyle}>
                    <div class="post-card active">
                        <div class="post-header">
                            <div class="post-profile">
                                <img src="${post.profile_pic}" class="profile-img" alt="Profile">
                                <div class="profile-info" style="color: ${post.text_color};">
                                    ${post.username} <span class="post-time" style="color: ${post.text_color}; opacity: 0.7;">• Posted</span>
                                </div>
                            </div>
                            ${optionsDropdownHTML}
                        </div>
                        <div class="post-body">
                            <div contenteditable="false" class="quote-text input-box" style="color: ${post.text_color};">${post.content}</div>
                        </div>
                        <div class="post-footer">
                            <div class="like-container-widget" onclick="toggleLikeNote(${post.id}, this)">
                                <svg class="footer-icon-svg heart-icon ${heartActiveClass}" viewBox="0 0 24 24" style="width:20px; height:20px; fill:#ccc; cursor:pointer; transition: fill 0.2s;">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <span class="like-counter-display ${counterActiveClass}" style="margin-left: 5px; font-weight:600; color:${post.text_color};">${post.likes || 0}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html_temp);
        });
    });
}

function addPost() {
    let target_ele = document.getElementById("newPostContent");
    let target_text = target_ele.value.trim();
    
    if (target_text === "") {
        alert("Please write something before posting!");
        return;
    }

    let activeTemplateClass = "tpl-yellow"; 
    if (selectedBgImage) {
        activeTemplateClass = "custom-user-image"; 
    } else if (chosenBgColor) {
        activeTemplateClass = "custom-color-mode";
    }

    const payload = {
        content: target_text,
        profile_pic: currentSessionAvatar,
        bg_image: selectedBgImage,
        bg_color: chosenBgColor,
        text_color: chosenTextColor,
        template_class: activeTemplateClass
    };

    fetch("posts_api.php?action=create", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            // Refresh feed in place
            target_ele.value = "";
            if (document.getElementById("charCounter")) document.getElementById("charCounter").textContent = "0 / 300";
            updateCreationStatus(null);
            loadDatabaseFeed(); 
        } else {
            alert("Failed saving sticky note data.");
        }
    });
}

function deletePost(id) {
    if (confirm("Are you sure you want to delete this note?")) {
        fetch(`posts_api.php?action=delete&id=${id}`, {
            method: 'GET'
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                const postElement = document.getElementById("postKey" + id);
                if (postElement) postElement.remove();
            } else {
                alert("Could not remove note object: " + data.message);
            }
        })
        .catch(err => {
            console.error("Deletion communication error:", err);
            alert("Failed to process delete operation parameters.");
        });
    }
}
