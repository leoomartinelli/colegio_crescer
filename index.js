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
       4. CUSTOM PLAYLIST AUDIO PLAYER
       ========================================================================== */
    
    // Player DOM elements
    const vinylWrapper = document.getElementById('vinylWrapper');
    const playlistBadge = document.getElementById('playlistBadge');
    const trackTitle = document.getElementById('trackTitle');
    const trackArtist = document.getElementById('trackArtist');
    const progressContainer = document.getElementById('progressContainer');
    const progressBarWrapper = document.getElementById('progressBarWrapper');
    const progressBar = document.getElementById('progressBar');
    const currentTimeEl = document.getElementById('currentTime');
    const totalDurationEl = document.getElementById('totalDuration');
    const prevBtn = document.getElementById('prevBtn');
    const playBtn = document.getElementById('playBtn');
    const nextBtn = document.getElementById('nextBtn');
    const muteBtn = document.getElementById('muteBtn');
    const volumeSliderWrapper = document.getElementById('volumeSliderWrapper');
    const volumeSlider = document.getElementById('volumeSlider');
    const playlistDrawer = document.getElementById('playlistDrawer');
    const playlistSongs = document.getElementById('playlistSongs');
    const songCountEl = document.getElementById('songCount');

    // Audio instance
    const audio = new Audio();
    audio.volume = 0.8; // default volume
    let isPlaying = false;
    let songsList = [];
    let currentSongIndex = 0;

    // Load songs list from PHP
    loadSongs();

    // Local file selector handler to load files directly (bypassing CORS for local development)
    const localFileSelector = document.getElementById('localFileSelector');
    if (localFileSelector) {
        localFileSelector.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length > 0) {
                songsList = Array.from(files).map(file => {
                    // Clean file name to use as title
                    let title = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
                    title = title.replace(/ytdown|ytmp3|youtube/gi, "").trim();
                    title = title.charAt(0).toUpperCase() + title.slice(1);
                    return {
                        title: title || "Música Local",
                        artist: "Upload Local",
                        src: URL.createObjectURL(file)
                    };
                });
                currentSongIndex = 0;
                renderPlaylist();
                loadSong(0);
                playAudio();
            }
        });
    }

    // Load songs from PHP server
    function loadSongs() {
        fetch('get_songs.php')
            .then(res => res.json())
            .then(data => {
                songsList = data;
                renderPlaylist();
                if (songsList.length > 0) {
                    loadSong(currentSongIndex);
                } else {
                    trackTitle.textContent = "Nenhuma Música";
                    trackArtist.textContent = "Adicione MP3s na pasta musicas/";
                }
            })
            .catch(err => {
                console.warn("Erro ao carregar via PHP (rodando localmente). Usando lista de músicas padrão:", err);
                songsList = [
                    {
                        title: "Olha Bela",
                        artist: "Lagum",
                        src: "musicas/YTDown.com_YouTube_Lagum-Olha-Bela-Clipe-Oficial-faixa4_Media_B3amFvHXdGs_009_128k.mp3"
                    },
                    {
                        title: "Eterno Agora",
                        artist: "Lagum",
                        src: "musicas/YTDown_YouTube_LAGUM-Eterno-Agora_Media_qHVkJ1Nloik_009_128k.mp3"
                    },
                    {
                        title: "Partilhar",
                        artist: "Rubel & ANAVITÓRIA",
                        src: "musicas/YouTube_Rubel-_-ANAVITORIA-Partilhar-Audio_Media_hlAjJAlN7u0_009_128k.mp3"
                    },
                    {
                        title: "Caderno",
                        artist: "Toquinho",
                        src: "musicas/Ytmp3.gg_YouTube_Caderno-Toquinho-letra-legendado_Media_-Gsu751X6ag_008_128k.mp3"
                    },
                    {
                        title: "Cake By The Ocean",
                        artist: "DNCE",
                        src: "musicas/Ytmp3.gg_YouTube_DNCE-Cake-By-The-Ocean_Media_PAzH-YAlFYc_009_128k.mp3"
                    }
                ];
                renderPlaylist();
                if (songsList.length > 0) {
                    loadSong(currentSongIndex);
                }
            });
    }

    // Render playlist HTML
    function renderPlaylist() {
        playlistSongs.innerHTML = '';
        
        if (songsList.length === 0) {
            songCountEl.textContent = "(0 músicas)";
            playlistSongs.innerHTML = `
                <li class="playlist-placeholder">
                    Nenhum arquivo .mp3 encontrado na pasta 'musicas/'. <br>
                    Envie músicas para o servidor para tocar na playlist.
                </li>
            `;
            return;
        }

        songCountEl.textContent = `(${songsList.length} ${songsList.length === 1 ? 'música' : 'músicas'})`;
        
        songsList.forEach((song, index) => {
            const li = document.createElement('li');
            li.dataset.index = index;
            if (index === currentSongIndex) {
                li.classList.add('active-song');
            }
            
            li.innerHTML = `
                <div class="song-info">
                    <span class="song-item-title">${song.title}</span>
                    <span class="song-item-artist">${song.artist}</span>
                </div>
                <div class="song-play-icon">
                    <i class="fa-solid ${index === currentSongIndex && isPlaying ? 'fa-pause' : 'fa-play'}"></i>
                </div>
            `;
            
            li.addEventListener('click', () => {
                if (currentSongIndex === index && isPlaying) {
                    pauseAudio();
                } else {
                    currentSongIndex = index;
                    loadSong(currentSongIndex);
                    playAudio();
                }
            });
            
            playlistSongs.appendChild(li);
        });
    }

    // Load a specific song from playlist
    function loadSong(index) {
        if (!songsList[index]) return;
        currentSongIndex = index;
        audio.src = songsList[index].src;
        trackTitle.textContent = songsList[index].title;
        trackArtist.textContent = songsList[index].artist;
        
        // Highlight active song in playlist
        const listItems = playlistSongs.querySelectorAll('li');
        listItems.forEach((li, idx) => {
            if (idx === index) {
                li.classList.add('active-song');
                li.querySelector('.song-play-icon i').className = isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play';
            } else {
                li.classList.remove('active-song');
                li.querySelector('.song-play-icon i').className = 'fa-solid fa-play';
            }
        });
        
        // Reset progress
        progressBar.style.width = '0%';
        currentTimeEl.textContent = "00:00";
    }

    // Playback control functions
    function playAudio() {
        audio.play()
            .then(() => {
                isPlaying = true;
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                vinylWrapper.classList.add('playing');
                vinylWrapper.style.animationPlayState = 'running';
                
                // Update playlist icons
                updatePlaylistPlayIcons(true);
            })
            .catch(err => {
                console.error("Falha ao tocar áudio:", err);
                alert("Não foi possível reproduzir o áudio.");
            });
    }

    function pauseAudio() {
        audio.pause();
        isPlaying = false;
        playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        vinylWrapper.style.animationPlayState = 'paused';
        
        updatePlaylistPlayIcons(false);
    }

    function updatePlaylistPlayIcons(isPlayingState) {
        const listItems = playlistSongs.querySelectorAll('li');
        listItems.forEach((li, idx) => {
            if (idx === currentSongIndex) {
                li.querySelector('.song-play-icon i').className = isPlayingState ? 'fa-solid fa-pause' : 'fa-solid fa-play';
            }
        });
    }

    // Play/Pause button trigger
    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            pauseAudio();
        } else {
            if (songsList.length > 0) {
                playAudio();
            }
        }
    });

    // Skip controls
    prevBtn.addEventListener('click', () => {
        if (songsList.length === 0) return;
        let prevIndex = currentSongIndex - 1;
        if (prevIndex < 0) prevIndex = songsList.length - 1;
        loadSong(prevIndex);
        if (isPlaying) playAudio();
    });

    nextBtn.addEventListener('click', () => {
        if (songsList.length === 0) return;
        let nextIndex = currentSongIndex + 1;
        if (nextIndex >= songsList.length) nextIndex = 0;
        loadSong(nextIndex);
        if (isPlaying) playAudio();
    });

    // Handle end of song (auto-advance)
    audio.addEventListener('ended', () => {
        let nextIndex = currentSongIndex + 1;
        if (nextIndex >= songsList.length) {
            nextIndex = 0;
            loadSong(nextIndex);
            pauseAudio();
        } else {
            loadSong(nextIndex);
            playAudio();
        }
    });

    // Audio time update (for progress slider)
    audio.addEventListener('timeupdate', () => {
        if (isNaN(audio.duration)) return;
        
        const current = audio.currentTime;
        const duration = audio.duration;
        const progressPercent = (current / duration) * 100;
        
        progressBar.style.width = `${progressPercent}%`;
        currentTimeEl.textContent = formatTime(current);
        totalDurationEl.textContent = formatTime(duration);
    });

    // Formatting MM:SS
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Click on progress bar to seek
    progressBarWrapper.addEventListener('click', (e) => {
        if (isNaN(audio.duration)) return;
        
        const wrapperWidth = progressBarWrapper.clientWidth;
        const clickX = e.offsetX;
        const duration = audio.duration;
        
        audio.currentTime = (clickX / wrapperWidth) * duration;
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
