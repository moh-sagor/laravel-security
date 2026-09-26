# Upload Security & File Inspection

The upload security module validates uploaded files using a multi-layer inspection process:

```
Upload -> Filename Validation -> Extension Check -> Magic-Byte Signature -> Decompression Bomb Check -> Quarantine / Allow
```

## Magic Byte Signature Inspection

File extensions can easily be forged (e.g. `web_shell.php` renamed to `avatar.jpg`). The package reads actual raw file header bytes to confirm format authenticity and rejects embedded web script payloads inside image files.

## Quarantine Storage

Infected or suspicious file uploads are moved into non-public quarantine storage at `storage/app/security/quarantine/` with a `.htaccess` file blocking execution. A `.json` sidecar file records the original filename, sha1 hash, size, and threat reason.
