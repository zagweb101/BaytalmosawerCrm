# نشر CRM تلقائيًا على Hostinger

هذا المشروع يستخدم GitHub Actions للنشر على:

`https://crm.baytalmosawer.net`

بعد الإعداد مرة واحدة، أي تعديل يتم رفعه إلى فرع `main` سيصل إلى الموقع تلقائيًا.

## 1. إنشاء Repository على GitHub

أنشئ Repository جديد باسم مناسب، مثل:

`bayt-crm`

ثم من جهازك المحلي داخل مجلد المشروع:

```powershell
cd "C:\Users\alhay\Documents\Codex\2026-06-08\crm-by-larvel-php-sql\crm"
git init
git branch -M main
git add .
git commit -m "Initial CRM release"
git remote add origin https://github.com/YOUR_USERNAME/bayt-crm.git
git push -u origin main
```

استبدل `YOUR_USERNAME` باسم حساب GitHub.

## 2. إعداد SSH Key للنشر

على جهازك المحلي:

```powershell
ssh-keygen -t ed25519 -C "github-actions-hostinger-crm" -f "$env:USERPROFILE\.ssh\hostinger_github_actions"
```

انسخ المفتاح العام:

```powershell
Get-Content "$env:USERPROFILE\.ssh\hostinger_github_actions.pub"
```

ضعه على السيرفر داخل:

```bash
mkdir -p ~/.ssh
nano ~/.ssh/authorized_keys
```

الصق المفتاح العام في سطر جديد، ثم:

```bash
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

## 3. إضافة أسرار GitHub

في GitHub Repository:

Settings -> Secrets and variables -> Actions -> New repository secret

أضف هذه الأسرار:

```text
HOSTINGER_HOST=185.237.147.43
HOSTINGER_PORT=65002
HOSTINGER_USER=u166250023
HOSTINGER_SSH_KEY=محتوى المفتاح الخاص
```

لنسخ المفتاح الخاص من جهازك:

```powershell
Get-Content "$env:USERPROFILE\.ssh\hostinger_github_actions"
```

## 4. أول نشر

بعد وضع الأسرار، ادفع أي تعديل:

```powershell
git add .
git commit -m "Enable automated deployment"
git push
```

ثم افتح تبويب Actions في GitHub وتابع عملية النشر.

## ملاحظات مهمة

- لا ترفع ملف `.env` إلى GitHub.
- ملف `.env` يبقى موجودًا فقط على السيرفر داخل:
  `/home/u166250023/domains/crm.baytalmosawer.net/crm_app/.env`
- لا يتم رفع مجلد `vendor` إلى GitHub؛ السيرفر يثبت الحزم تلقائيًا.
- النشر يحدث بعد `git push`، وليس بمجرد حفظ الملف محليًا.

Last deployment test: 06/10/2026 12:09:45
