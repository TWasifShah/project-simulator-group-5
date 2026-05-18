function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}
function showMessage(el, msg, isError = false) {
    if (!el) return;
    el.textContent = msg;
    el.style.color = isError ? '#c1121f' : '#657083';
}
function escapeHtml(str) {
    return String(str || '').replace(/[&<>"]/g, function (s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[s];
    });
}
function validateRequired(form) {
    let ok = true;
    form.querySelectorAll('[required]').forEach(input => {
        const err = input.parentElement.querySelector('.field-error') || input.nextElementSibling;
        if (!input.value.trim()) {
            ok = false;
            if (err && err.classList.contains('field-error')) err.textContent = 'This field is required.';
        }
    });
    return ok;
}

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    if (menuToggle && mainNav) menuToggle.addEventListener('click', () => mainNav.classList.toggle('open'));

    const registerForm = document.getElementById('registerForm');
    if (registerForm) registerForm.addEventListener('submit', e => {
        let ok = validateRequired(registerForm);
        const email = registerForm.email.value.trim();
        const pass = registerForm.password.value;
        const confirm = registerForm.confirm_password.value;
        if (!/^\S+@\S+\.\S+$/.test(email)) { ok = false; registerForm.email.nextElementSibling.textContent = 'Enter a valid email.'; }
        if (pass.length < 8) { ok = false; registerForm.password.nextElementSibling.textContent = 'Password must be at least 8 characters.'; }
        if (pass !== confirm) { ok = false; registerForm.confirm_password.nextElementSibling.textContent = 'Passwords do not match.'; }
        if (!ok) e.preventDefault();
    });

    const profileForm = document.getElementById('profileForm');
    if (profileForm) profileForm.addEventListener('submit', e => {
        let ok = validateRequired(profileForm);
        const email = profileForm.email.value.trim();
        if (!/^\S+@\S+\.\S+$/.test(email)) { ok = false; profileForm.email.nextElementSibling.textContent = 'Enter a valid email.'; }
        const newPass = profileForm.new_password.value;
        const confirm = profileForm.confirm_password.value;
        if (newPass && newPass.length < 8) { ok = false; profileForm.new_password.nextElementSibling.textContent = 'New password must be at least 8 characters.'; }
        if (newPass !== confirm) { ok = false; profileForm.confirm_password.nextElementSibling.textContent = 'Passwords do not match.'; }
        if (!ok) e.preventDefault();
    });

    const restaurantForm = document.getElementById('restaurantForm');
    if (restaurantForm) restaurantForm.addEventListener('submit', e => { if (!validateRequired(restaurantForm)) e.preventDefault(); });

    const menuItemForm = document.getElementById('menuItemForm');
    if (menuItemForm) menuItemForm.addEventListener('submit', e => {
        let ok = validateRequired(menuItemForm);
        const price = parseFloat(menuItemForm.price.value);
        if (isNaN(price) || price <= 0) { ok = false; menuItemForm.price.nextElementSibling.textContent = 'Price must be greater than 0.'; }
        const file = menuItemForm.image.files[0];
        if (file) {
            if (!['image/jpeg', 'image/png'].includes(file.type)) { ok = false; menuItemForm.image.nextElementSibling.textContent = 'Only JPEG and PNG allowed.'; }
            if (file.size > 2 * 1024 * 1024) { ok = false; menuItemForm.image.nextElementSibling.textContent = 'Image must be 2MB or less.'; }
        }
        if (!ok) e.preventDefault();
    });

    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', async e => {
            e.preventDefault();
            const data = new FormData(searchForm);
            const params = new URLSearchParams(data);
            const msg = document.getElementById('searchMessage');
            const box = document.getElementById('searchResults');
            showMessage(msg, 'Searching...');
            const res = await fetch('index.php?route=api/search&' + params.toString());
            const json = await res.json();
            if (!json.success) { showMessage(msg, json.message || 'Search failed.', true); return; }
            showMessage(msg, `Found ${json.restaurants.length} restaurants and ${json.items.length} food items.`);
            box.innerHTML = renderSearchResults(json.restaurants, json.items);
        });
    }

    function renderSearchResults(restaurants, items) {
        const restaurantCards = restaurants.map(r => `<article class="card"><h3>${r.name}</h3><p class="muted">${r.location}, ${r.area}</p><p>${r.short_background}</p><a class="text-link" href="${r.url}">Open Restaurant</a></article>`).join('');
        const itemCards = items.map(i => `<article class="card item-card">${i.image_path ? `<img src="${i.image_path}" alt="${i.name}">` : ''}<h3>${i.name}</h3><p class="muted">${i.restaurant_name} | ${i.location}, ${i.area}</p><p>${i.description}</p><p class="price">Tk ${i.price}</p><a class="text-link" href="${i.url}">View Details</a></article>`).join('');
        return `<div class="search-block"><h3>Restaurants</h3><div class="search-grid">${restaurantCards || '<p class="muted">No restaurants found.</p>'}</div></div><div class="search-block"><h3>Food Items</h3><div class="search-grid">${itemCards || '<p class="muted">No food items found.</p>'}</div></div>`;
    }

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) reviewForm.addEventListener('submit', async e => {
        e.preventDefault();
        const comment = reviewForm.comment.value.trim();
        if (!comment) { alert('Comment is required.'); return; }
        const formData = new FormData(reviewForm);
        const res = await fetch('index.php?route=api/reviews/add', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken() }, body: formData });
        const json = await res.json();
        if (!json.success) { alert(json.message); return; }
        const list = document.getElementById('reviewsList');
        list.insertAdjacentHTML('afterbegin', `<div class="comment" data-review-id="${json.review.id}"><strong>${json.review.user_name}</strong><p>${json.review.comment}</p><small>${json.review.created_at}</small> <button class="link-danger delete-review" data-id="${json.review.id}" type="button">Delete</button></div>`);
        reviewForm.reset();
    });

    document.addEventListener('click', async e => {
        if (e.target.classList.contains('delete-review')) {
            if (!confirm('Delete this review?')) return;
            const id = e.target.dataset.id;
            const formData = new FormData();
            formData.append('id', id);
            const res = await fetch('index.php?route=api/reviews/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken() }, body: formData });
            const json = await res.json();
            if (!json.success) { alert(json.message); return; }
            e.target.closest('.comment').remove();
        }
    });

    const restaurantReviewForm = document.getElementById('restaurantReviewForm');
    if (restaurantReviewForm) restaurantReviewForm.addEventListener('submit', async e => {
        e.preventDefault();
        if (!restaurantReviewForm.comment.value.trim()) { alert('Comment is required.'); return; }
        const formData = new FormData(restaurantReviewForm);
        const res = await fetch('index.php?route=api/restaurant-reviews/add', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken() }, body: formData });
        const json = await res.json();
        if (!json.success) { alert(json.message); return; }
        document.getElementById('restaurantReviews').insertAdjacentHTML('afterbegin', `<div class="comment"><strong>${json.review.user_name}</strong> <span class="badge">Rating ${json.review.rating}/5</span><p>${json.review.comment}</p><small>${json.review.created_at}</small></div>`);
        restaurantReviewForm.reset();
    });

    const foodPostForm = document.getElementById('foodPostForm');
    if (foodPostForm) foodPostForm.addEventListener('submit', e => { if (!validateRequired(foodPostForm)) e.preventDefault(); });

    const foodCommentForm = document.getElementById('foodCommentForm');
    if (foodCommentForm) foodCommentForm.addEventListener('submit', async e => {
        e.preventDefault();
        const comment = foodCommentForm.comment.value.trim();
        if (!comment) { alert('Comment is required.'); return; }
        const formData = new FormData(foodCommentForm);
        const res = await fetch('index.php?route=api/food-exp/comments/add', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken() }, body: formData });
        const json = await res.json();
        if (!json.success) { alert(json.message); return; }
        document.getElementById('foodCommentsList').insertAdjacentHTML('beforeend', `<div class="comment" data-comment-id="${json.comment.id}"><strong>${json.comment.user_name}</strong><p>${json.comment.comment}</p><small>${json.comment.created_at}</small> <button class="link-danger delete-food-comment" data-id="${json.comment.id}" type="button">Delete</button></div>`);
        foodCommentForm.reset();
    });

    document.addEventListener('click', async e => {
        if (e.target.classList.contains('delete-food-comment')) {
            if (!confirm('Delete this comment?')) return;
            const formData = new FormData();
            formData.append('id', e.target.dataset.id);
            const res = await fetch('index.php?route=api/food-exp/comments/delete', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken() }, body: formData });
            const json = await res.json();
            if (!json.success) { alert(json.message); return; }
            e.target.closest('.comment').remove();
        }
    });
});
