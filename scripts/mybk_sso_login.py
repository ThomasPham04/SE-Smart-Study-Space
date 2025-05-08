import sys
import json
import requests
import base64
from bs4 import BeautifulSoup

def decode_jwt_payload(jwt):
    payload = jwt.split(".")[1]
    payload += "=" * (-len(payload) % 4)
    decoded_bytes = base64.urlsafe_b64decode(payload)
    decoded_str = decoded_bytes.decode("utf-8")
    return json.loads(decoded_str)

def get_information(user, passs):
    login_data = {
        'username': user,
        'password': passs,
        '_eventId': 'submit',
        'submit': 'Login',
    }

    with requests.Session() as S:
        S.cookies.clear()
        headers = {
            'User-Agent': 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36 Edg/126.0.0.0'
        }

        login_url = 'https://sso.hcmut.edu.vn/cas/login?service=https%3A%2F%2Fmybk.hcmut.edu.vn%2Fapp%2Flogin%2Fcas'
        home_url = 'https://mybk.hcmut.edu.vn/app/'
        r = S.get(login_url, headers=headers)
        soup = BeautifulSoup(r.content, 'html5lib')

        login_data['lt'] = soup.find('input', attrs={'name': 'lt'})['value']
        login_data['execution'] = soup.find('input', attrs={'name': 'execution'})['value']
        r = S.post(login_url, data=login_data)

        if r.url == home_url:
            uni_records_url = 'https://mybk.hcmut.edu.vn/app/he-thong-quan-ly/sinh-vien/ket-qua-hoc-tap'
            response_records = S.get(uni_records_url)
            soup1 = BeautifulSoup(response_records.content, 'html5lib')
            token = soup1.find('input', attrs={'id': 'hid_Token'})['value']

            json_data = decode_jwt_payload(token)
            profiles = json_data['profiles']
            person_ID = json.loads(profiles)['personId']

            url_mark = f"https://mybk.hcmut.edu.vn/api/sinh-vien/xem-ket-qua-hoc-tap/v2?mssv={person_ID}&null"
            url_full_info = f"https://mybk.hcmut.edu.vn/api/v1/student/detail-info-by-code/{person_ID}?null"

            if response_records.status_code == 200:
                headers = {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }

                response_info = S.get(url_full_info, headers=headers)
                full_info = response_info.json()

                response_info = S.get(url_mark, headers=headers)
                mark = response_info.json()
                data = full_info['data']
                code = data['code']
                lastName = data['lastName']
                firstName = data['firstName']
                orgEmail = data['orgEmail']

                info = {
                    'code': code,
                    'lastName': lastName,
                    'firstName': firstName,
                    'orgEmail': orgEmail
                }
                return {"status": "success", "full_info": info}
        return {"status": "failed"}

if __name__ == '__main__':
    username = sys.argv[1]
    password = sys.argv[2]
    result = get_information(username, password)
    print(json.dumps(result)) 