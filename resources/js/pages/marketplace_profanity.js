import { findProfanity } from '../utils/profanity_checker';

document.addEventListener('DOMContentLoaded', () => {

    loadPosts();

    setupCreatePost();
});

// LOAD POSTS
function loadPosts() {

    fetch('/posts')
        .then(res => res.json())
        .then(posts => {

            const container = document.getElementById('marketplaceCardsContainer');
            const emptyState = document.getElementById('marketplaceEmptyState');

            container.innerHTML = '';

            if (!posts.length) {
                if (emptyState) emptyState.classList.remove('d-none');
                return;
            }

            if (emptyState) emptyState.classList.add('d-none');

            posts.forEach(post => {

                container.innerHTML += `
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">

                        <img src="${post.photo_1_url ? '/storage/' + post.photo_1_url : '/images/default.png'}"
                             class="w-100"
                             style="height:220px; object-fit:cover;">

                        <div class="p-3 d-flex flex-column h-100">

                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">${post.title}</h6>
                                <span class="badge px-3 py-2" style="background:#6FC21F;color:white;">
                                    ${post.status}
                                </span>
                            </div>

                            <div class="fw-bold fs-4 text-success mb-2">
                                $${parseFloat(post.cost).toFixed(2)}
                            </div>

                            <div class="d-flex gap-2 mb-2 flex-wrap">
                                <span class="badge px-3 py-2" style="background:#6FC21F;color:white;">
                                    ${post.condition}
                                </span>
                                <span class="badge px-3 py-2" style="background:#6FC21F;color:white;">
                                    ${post.category}
                                </span>
                            </div>

                            <div class="text-muted small mb-1">
                                <i class="bi bi-person"></i>
                                    ${post.user ? post.user.name : 'Usuario'}
                            </div>

                            <div class="text-muted small mb-3">
                                <i class="bi bi-clock"></i>
                                ${timeAgo(post.created_at)}
                            </div>

                            <div class="mt-auto">

                                <button class="btn btn-success w-100 mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#postDetailsModal"
                                            onclick="loadPost(this)"
                                            data-post="${encodeURIComponent(JSON.stringify(post))}">
                                    Ver Detalles
                                </button>

                                ${
                                    post.user_id === window.authUserId
                                    ? `<button class="btn btn-danger w-100"
                                            onclick="deletePost(${post.id})">
                                            Eliminar
                                       </button>`
                                    : ''
                                }

                            </div>

                        </div>
                    </div>
                </div>
                `;
            });

        });
}

// DELETE POST
function deletePost(id){

    if(!confirm('¿Eliminar publicación?')) return;

    fetch(`/posts/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => loadPosts());
}

// MODAL
window.loadPost = function(el){

    let post = el.dataset.post;

    try {
        post = JSON.parse(decodeURIComponent(post));
    } catch (e) {
        console.error('Error parsing post:', e);
        return;
    }

    document.getElementById('postDetailsModalLabel').innerText = post.title;

    const desc = document.getElementById('postDetailsDescription');

    if(post.description){
        desc.innerText = post.description;
        desc.classList.remove('d-none');
    } else {
        desc.classList.add('d-none');
    }

    document.getElementById('postDetailsPrice').innerText =
        '$' + parseFloat(post.cost).toFixed(2);

    document.getElementById('postDetailsSeller').innerText =
        post.user ? post.user.name : 'Usuario';

    // CAROUSEL
    const inner = document.getElementById('postImagesCarouselInner');
    const indicators = document.getElementById('postImagesCarouselIndicators');

    inner.innerHTML = '';
    indicators.innerHTML = '';

    const images = [
        post.photo_1_url,
        post.photo_2_url,
        post.photo_3_url
    ].filter(Boolean);

    images.forEach((img, index) => {

        inner.innerHTML += `
        <div class="carousel-item ${index === 0 ? 'active' : ''}">
            <img src="/storage/${img}" class="d-block w-100">
        </div>`;

        indicators.innerHTML += `
        <button data-bs-target="#postImagesCarousel"
                data-bs-slide-to="${index}"
                class="${index === 0 ? 'active' : ''}">
        </button>`;
    });
};


function setupCreatePost(){

    const publishBtn = document.getElementById('publishBtn');

    if (!publishBtn) return;

    const title = document.getElementById('postTitle');
    const description = document.getElementById('postDescription');
    const price = document.getElementById('postPrice');
    const category = document.getElementById('postCategory');
    const condition = document.getElementById('postCondition');
    const images = document.getElementById('postImage');

    publishBtn.addEventListener('click', async () => {
        // VALIDACIÓN
        if(findProfanity(title.value) || findProfanity(description.value)){
            alert('Lenguaje inapropiado detectado');
            return;
        }

        const formData = new FormData();

        formData.append('title', title.value);
        formData.append('description', description.value);
        formData.append('cost', price.value);
        formData.append('category', category.value);
        formData.append('condition', condition.value);

        for(let i = 0; i < images.files.length; i++){
            formData.append('images[]', images.files[i]);
        }

        try {

            const res = await fetch('/posts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            if (!res.ok) {
                const error = await res.text();
                console.error('ERROR BACKEND:', error);
                alert('Error al guardar');
                return;
            }

            // cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
            modal.hide();

            // recargar posts
            loadPosts();

        } catch (err){
            console.error(err);
        }

        const matchedWord = findProfanity(value);

        if (matchedWord) {
            setProfanityPriority(
                descriptionInput,
                descriptionBaseError,
                descriptionProfanityError,
                'La descripción contiene lenguaje inapropiado.'
            );
            return false;
        }

        return true;
    });
}


    function enforceProfanityPriority() {
        const titleHasProfanity = titleProfanityError.textContent.trim() !== '';
        const descriptionHasProfanity = descriptionProfanityError.textContent.trim() !== '';

        if (titleHasProfanity) {
            titleBaseError.textContent = '';
            titleInput.classList.add('is-invalid');
        }

        if (descriptionHasProfanity) {
            descriptionBaseError.textContent = '';
            descriptionInput.classList.add('is-invalid');
        }
    }

    function updateProfanityState() {
        validateTitleProfanity();
        validateDescriptionProfanity();
        enforceProfanityPriority();
    }

    function runAfterBaseValidation(callback) {
        setTimeout(callback, 0);
    }

    titleInput.addEventListener('input', () => {
        runAfterBaseValidation(updateProfanityState);
    });



function timeAgo(date){
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);

    if(seconds < 60) return 'hace segundos';
    if(seconds < 3600) return Math.floor(seconds/60) + ' min';
    if(seconds < 86400) return Math.floor(seconds/3600) + ' h';

    return Math.floor(seconds/86400) + ' días';
}