#!/usr/bin/env python3
import pathlib

path = pathlib.Path("/data/coolify/applications/psw0x17s3rhdp8yav3n899yo/docker-compose.yaml")
text = path.read_text()

dual = (
    "(Host(`pescaderia.amjsoft.com`) || Host(`pescaderia.nbsoporteti.com`)) && PathPrefix(`/`)"
)
single = "Host(`pescaderia.amjsoft.com`) && PathPrefix(`/`)"

if single in text and dual not in text:
    print("already single-host")
elif dual in text:
    path.write_text(text.replace(dual, single))
    print("reverted to pescaderia.amjsoft.com only")
else:
    raise SystemExit("unexpected rule format")
