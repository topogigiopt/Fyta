export default function request(props, useFormData) {
    return new Promise(async function (resolve, reject) {
        let { url, method, content } = props;
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        };

        if (method === 'GET') {
            content && (url +=
                '?' +
                Object.keys(content)
                    .map(key => `${key}=${content[key]}`)
                    .join('&'));
        } else if (useFormData) {
            options.body = new FormData();
            Object.keys(content).forEach(key => options.body.append(key, content[key]));
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(content);
        }

        const response = await fetch(url, options)
            .then(res => {
                return { status: res.status, result: res.json() };
            })
            .catch(() => reject('Unkown error'));

        response.result
            .then(data => {
                data.status = response.status
                if (response.status == 200 || response.status == 201) {
                    resolve(data);
                } else {
                    reject(data);
                }
            })
            .catch(() => {
                resolve(response);
            });
    });
}

export const fetchData = async (url, page = 1) => {
    const response = await request({ url, method: 'GET', content : { page } })
    return response
}

export const postData = async (url, quantity) => {
    const response = await request({ url, method: 'POST', content: { 'quantity': quantity } })
    return response
}

export const deleteData = async(url) => {
    const response = await request({
        url,
        method: 'DELETE',
    })
    return response
}

export const deleteAccount = async(url,reason) => {
    const response = await request({
        url,
        method: 'DELETE',
        content: { 'reason': reason }
    })
    return response
}
