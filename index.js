/* ==========================================================================
   COLEGIO CRESCER - INTERACTIVE SCRIPTS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================================
       1. NAVIGATION & STICKY HEADER
       ========================================================================== */
    const header = document.getElementById('header');
    const mobileNavToggle = document.getElementById('mobileNavToggle');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Sticky header shrink on scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Back to Top button toggle
        toggleBackToTop();
    });

    // Mobile nav toggle
    mobileNavToggle.addEventListener('click', () => {
        navMenu.classList.toggle('open');
        mobileNavToggle.classList.toggle('active');
        
        // Hamburger animation toggle
        const bars = mobileNavToggle.querySelectorAll('.bar');
        if (navMenu.classList.contains('open')) {
            bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
            bars[1].style.opacity = '0';
            bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
        } else {
            bars[0].style.transform = 'none';
            bars[1].style.opacity = '1';
            bars[2].style.transform = 'none';
        }
    });

    // Close menu when clicking link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('open');
            mobileNavToggle.classList.remove('active');
            const bars = mobileNavToggle.querySelectorAll('.bar');
            bars[0].style.transform = 'none';
            bars[1].style.opacity = '1';
            bars[2].style.transform = 'none';
            
            // Highlight active link manually on click
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // Scroll Spy: Highlight active nav link on scroll
    const sections = document.querySelectorAll('section, header');
    window.addEventListener('scroll', () => {
        let scrollPosition = window.scrollY + 120; // offset
        
        sections.forEach(section => {
            if (section.id) {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    const activeId = section.getAttribute('id');
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${activeId}` || (activeId === 'home' && link.getAttribute('href') === '#')) {
                            link.classList.add('active');
                        }
                    });
                }
            }
        });
    });


    /* ==========================================================================
       2. MODAL & LIGHTBOX SYSTEM (VIDEO + GALLERY)
       ========================================================================== */
    const modalOverlay = document.getElementById('modalOverlay');
    const modalClose = document.getElementById('modalClose');
    const modalBodyContent = document.getElementById('modalBodyContent');
    const playVideoBtn = document.getElementById('playVideoBtn');

    // Open video modal
    if (playVideoBtn) {
        playVideoBtn.addEventListener('click', () => {
            modalBodyContent.innerHTML = `
                <video controls autoplay class="modal-video">
                    <source src="assets/video/Crescer.mp4" type="video/mp4">
                    Seu navegador não suporta a tag de vídeo.
                </video>
            `;
            openModal();
        });
    }

    // Open gallery image lightbox
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        const img = item.querySelector('.gallery-img');
        const title = item.querySelector('h4').textContent;
        const subtitle = item.querySelector('p').textContent;
        
        item.addEventListener('click', () => {
            modalBodyContent.innerHTML = `
                <div class="lightbox-content">
                    <img src="${img.src}" alt="${img.alt}">
                    <div style="padding: 15px 20px; background-color: white;">
                        <h4 style="margin:0; font-family:var(--font-heading); color:var(--primary-color);">${title}</h4>
                        <p style="margin:5px 0 0; font-size:0.9rem; color:var(--light-text);">${subtitle}</p>
                    </div>
                </div>
            `;
            openModal();
        });
    });

    function openModal() {
        modalOverlay.style.display = 'flex';
        // Trigger reflow for transition
        void modalOverlay.offsetWidth;
        modalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden'; // Lock scroll
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
        document.body.style.overflow = ''; // Restore scroll
        setTimeout(() => {
            modalOverlay.style.display = 'none';
            // Stop any playing video by clearing content
            modalBodyContent.innerHTML = '';
        }, 300);
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });


    /* ==========================================================================
       3. GALLERY CATEGORY FILTERS
       ========================================================================== */
    const filterButtons = document.querySelectorAll('.gallery-filter-btn');
    const items = document.querySelectorAll('.gallery-item');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Toggle active class
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            items.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    // Trigger simple transition
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    /* ==========================================================================
       4. CUSTOM PLAYLIST AUDIO PLAYER (SIMULATED LIVE RADIO)
       ========================================================================== */
    
    // Player DOM elements
    const vinylWrapper = document.getElementById('vinylWrapper');
    const playBtn = document.getElementById('playBtn');
    const muteBtn = document.getElementById('muteBtn');
    const volumeSliderWrapper = document.getElementById('volumeSliderWrapper');
    const volumeSlider = document.getElementById('volumeSlider');

    // Audio instance
    const audio = new Audio();
    audio.volume = 0.8; // default volume
    let isPlaying = false;
    let songsList = [];
    let currentSongIndex = 0;

    // Load songs list from PHP
    loadSongs();

    // Load songs from PHP server
    function loadSongs() {
        fetch('get_songs.php')
            .then(res => res.json())
            .then(data => {
                songsList = data;
                if (songsList.length > 0) {
                    loadSong(currentSongIndex);
                }
            })
            .catch(err => {
                console.warn("Erro ao carregar via PHP (rodando localmente):", err);
            });
    }

    // Load a specific song from playlist
    function loadSong(index) {
        if (!songsList[index]) return;
        currentSongIndex = index;
        audio.src = songsList[index].src;
    }

    // Playback control functions
    function playAudio() {
        if (songsList.length === 0) {
            alert("Nenhum arquivo de áudio encontrado no sistema (pasta musicas/).");
            return;
        }
        
        audio.play()
            .then(() => {
                isPlaying = true;
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                vinylWrapper.classList.add('playing');
                vinylWrapper.style.animationPlayState = 'running';
            })
            .catch(err => {
                console.error("Falha ao tocar áudio:", err);
                alert("Não foi possível reproduzir a rádio no momento.");
            });
    }

    function pauseAudio() {
        audio.pause();
        isPlaying = false;
        playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        vinylWrapper.classList.remove('playing');
        vinylWrapper.style.animationPlayState = 'paused';
    }

    // Play/Pause button trigger
    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            pauseAudio();
        } else {
            playAudio();
        }
    });

    // Handle end of song (auto-advance sequentially to simulate continuous stream)
    audio.addEventListener('ended', () => {
        let nextIndex = currentSongIndex + 1;
        if (nextIndex >= songsList.length) {
            nextIndex = 0;
        }
        loadSong(nextIndex);
        playAudio();
    });

    // Volume & Mute logic
    let savedVolume = 0.8;
    let isMuted = false;

    muteBtn.addEventListener('click', () => {
        if (isMuted) {
            audio.volume = savedVolume;
            volumeSlider.style.width = `${savedVolume * 100}%`;
            muteBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
            isMuted = false;
        } else {
            savedVolume = audio.volume;
            audio.volume = 0;
            volumeSlider.style.width = '0%';
            muteBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
            isMuted = true;
        }
    });

    volumeSliderWrapper.addEventListener('click', (e) => {
        const wrapperWidth = volumeSliderWrapper.clientWidth;
        const clickX = e.offsetX;
        let volume = clickX / wrapperWidth;
        
        // Bounds checking
        if (volume < 0) volume = 0;
        if (volume > 1) volume = 1;
        
        audio.volume = volume;
        volumeSlider.style.width = `${volume * 100}%`;
        isMuted = false;
        
        // Change volume icon dynamically
        if (volume === 0) {
            muteBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
            isMuted = true;
        } else if (volume < 0.5) {
            muteBtn.innerHTML = '<i class="fa-solid fa-volume-low"></i>';
        } else {
            muteBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
        }
    });


    /* ==========================================================================
       5. CONTACT FORM SUBMISSION
       ========================================================================== */
    const contactForm = document.getElementById('contactForm');
    const formStatus = document.getElementById('formStatus');

    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Gather inputs
            const name = document.getElementById('formName').value.trim();
            const email = document.getElementById('formEmail').value.trim();
            const phone = document.getElementById('formPhone').value.trim();
            const segment = document.getElementById('formSegment').value;
            const message = document.getElementById('formMessage').value.trim();
            
            // Show submitting status
            formStatus.className = 'form-status';
            formStatus.style.display = 'block';
            formStatus.textContent = 'Enviando sua mensagem...';
            
            // Simulate server validation / sending
            setTimeout(() => {
                if (name && email && phone && segment && message) {
                    formStatus.className = 'form-status success';
                    formStatus.innerHTML = '<i class="fa-solid fa-circle-check"></i> Mensagem enviada com sucesso! Entraremos em contato em breve.';
                    contactForm.reset();
                    
                    // Auto-hide success alert
                    setTimeout(() => {
                        formStatus.style.display = 'none';
                    }, 5000);
                } else {
                    formStatus.className = 'form-status error';
                    formStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Por favor, preencha todos os campos obrigatórios.';
                }
            }, 1500);
        });
    }


    /* ==========================================================================
       6. BACK TO TOP BUTTON
       ========================================================================== */
    const backToTopBtn = document.getElementById('backToTopBtn');

    function toggleBackToTop() {
        if (window.scrollY > 400) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

});
