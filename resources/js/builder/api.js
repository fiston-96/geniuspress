const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export async function api(url, { method = 'GET', body, form } = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' };
    let payload;
    if (form) payload = form;
    else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }
    const res = await fetch(url, { method, headers, body: payload, credentials: 'same-origin' });
    if (res.status === 204) return null;
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg = data?.message || (data?.errors && Object.values(data.errors)[0]?.[0]) || `Erreur ${res.status}`;
        throw new Error(res.status === 419 ? 'Session expirée : rechargez la page.' : msg);
    }
    return data;
}
