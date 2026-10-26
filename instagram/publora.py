#!/usr/bin/env python3
"""Ferramenta de publicacao da @forward.fs via Publora.

Le PUBLORA_API_KEY de ~/.claude/skills/instagram-skills/.env.
Nao publica nada: cria rascunho. Agendar exige midia enviada antes.

  publora.py canais
  publora.py rascunhos legendas.json [--plataformas instagram,tiktok,youtube]
  publora.py listar
"""
import argparse
import datetime, json, os, pathlib, sys, time, urllib.error, urllib.request

BASE = "https://api.publora.com/api/v1"
ENV = pathlib.Path.home() / ".claude/skills/instagram-skills/.env"


def chave():
    if k := os.environ.get("PUBLORA_API_KEY"):
        return k
    for linha in ENV.read_text().splitlines():
        if linha.startswith("PUBLORA_API_KEY="):
            return linha.split("=", 1)[1].strip()
    sys.exit(f"PUBLORA_API_KEY nao encontrada em {ENV}")


def req(caminho, corpo=None, metodo=None):
    r = urllib.request.Request(
        BASE + caminho,
        data=json.dumps(corpo).encode() if corpo else None,
        headers={
            "x-publora-key": chave(),
            "Content-Type": "application/json",
            # Sem User-Agent o Cloudflare da Publora devolve 403 code 1010.
            "User-Agent": "curl/8.5.0",
            "Accept": "*/*",
        },
        method=metodo or ("POST" if corpo else "GET"),
    )
    try:
        with urllib.request.urlopen(r, timeout=60) as resp:
            return json.load(resp)
    except urllib.error.HTTPError as e:
        sys.exit(f"HTTP {e.code}: {e.read().decode()[:300]}")


def canais():
    return req("/platform-connections").get("connections", [])


def ids(prefixos):
    mapa = {c["platformId"].split("-", 1)[0]: c["platformId"] for c in canais()}
    faltando = [p for p in prefixos if p not in mapa]
    if faltando:
        sys.exit(f"canal nao conectado: {', '.join(faltando)}. Conectados: {sorted(mapa)}")
    return [mapa[p] for p in prefixos]


def valida(itens):
    """Aborta sem gravar nada se qualquer legenda violar o padrao."""
    erros = []
    for x in itens:
        n, c = x.get("n", "?"), x.get("c", "")
        gancho = c.split("\n")[0]
        if not c.strip():
            erros.append(f"{n}: legenda vazia")
        if len(gancho) > 100:
            erros.append(f"{n}: gancho com {len(gancho)}ch, limite 100")
        if gancho.lower().startswith("salve"):
            erros.append(f"{n}: gancho abre com 'Salve'")
        if c.count("#") != 5:
            erros.append(f"{n}: {c.count('#')} hashtags, o padrao e 5")
        if len(c) > 2200:
            erros.append(f"{n}: {len(c)}ch, limite 2200 do Instagram")
    if erros:
        sys.exit("padrao violado, nada foi gravado:\n  " + "\n  ".join(erros))


def main():
    p = argparse.ArgumentParser()
    sub = p.add_subparsers(dest="cmd", required=True)
    sub.add_parser("canais")
    sub.add_parser("listar")
    r = sub.add_parser("rascunhos")
    r.add_argument("arquivo")
    r.add_argument("--plataformas", default="instagram")
    g = sub.add_parser("agendar")
    g.add_argument("arquivo")
    g.add_argument("--plataformas", default="instagram,tiktok,youtube")
    g.add_argument("--executar", action="store_true", help="sem isso so mostra o plano")
    a = p.parse_args()

    if a.cmd == "canais":
        for c in canais():
            print(f"{c['platformId']:50} @{c.get('username')} token={c.get('tokenStatus')}")
    elif a.cmd == "listar":
        print(json.dumps(req("/posts"), ensure_ascii=False, indent=1)[:4000])
    elif a.cmd == "rascunhos":
        itens = json.loads(pathlib.Path(a.arquivo).read_text())
        valida(itens)
        alvos = ids([s.strip() for s in a.plataformas.split(",") if s.strip()])
        print(f"{len(itens)} legendas validadas, destino: {alvos}")
        for x in itens:
            out = req("/create-post", {
                "content": x["c"], "platforms": alvos,
                "platformSettings": {"instagram": {"videoType": "REELS"}},
            })
            x["postGroupId"] = out.get("postGroupId")
            print(f"  {x.get('n')} {x.get('serie','')} -> {x['postGroupId']}")
        saida = pathlib.Path(a.arquivo).with_suffix(".criados.json")
        saida.write_text(json.dumps(itens, ensure_ascii=False, indent=1))
        print(f"ids gravados em {saida}")

    elif a.cmd == "agendar":
        agendar(a)


VIDEOS = pathlib.Path(__file__).parent / "videos"


def baixa(x):
    VIDEOS.mkdir(exist_ok=True)
    dest = VIDEOS / f"{x['n']:02d}-{x['serie']}.mp4"
    if not dest.exists():
        rq = urllib.request.Request(x["url_video"], headers={"User-Agent": "Mozilla/5.0"})
        with urllib.request.urlopen(rq, timeout=120) as r:
            dest.write_bytes(r.read())
    return dest


def espera_midia(pid, limite=600):
    """A Publora processa a midia depois do PUT; agendar antes disso falha."""
    t0 = time.time()
    while time.time() - t0 < limite:
        midia = req(f"/get-post/{pid}").get("media", [])
        estados = {m.get("status") for m in midia}
        if "failed" in estados or any(m.get("failureReason") for m in midia):
            sys.exit(f"{pid}: midia falhou na Publora: {midia}")
        if midia and "uploading" not in estados:
            return
        time.sleep(5)
    sys.exit(f"{pid}: midia ainda processando apos {limite}s, nada agendado")


def agendar(a):
    itens = json.loads(pathlib.Path(a.arquivo).read_text())
    valida(itens)
    agora = datetime.datetime.now(datetime.timezone.utc)
    for x in itens:
        q = datetime.datetime.fromisoformat(x["quando"].replace("Z", "+00:00"))
        if q < agora + datetime.timedelta(minutes=30):
            sys.exit(f"{x['n']}: horario {x['quando']} no passado ou perto demais")
        if len(x["titulo"]) > 100:
            sys.exit(f"{x['n']}: titulo do YouTube com {len(x['titulo'])}ch, limite 100")
    print(f"{len(itens)} itens validos, destino {a.plataformas}")
    for x in itens:
        print(f"  {x['n']} {x['quando']} {x['dur']}s | {x['titulo']}")
    if not a.executar:
        print("plano apenas. Use --executar para criar e agendar.")
        return
    alvos = ids([s.strip() for s in a.plataformas.split(",") if s.strip()])
    ajustes = {"instagram": {"videoType": "REELS"},
               "youtube": {"privacy": "public"},
               "tiktok": {"viewerSetting": "PUBLIC_TO_EVERYONE", "allowComments": True}}
    for x in itens:
        if x.get("postGroupId"):
            print(f"  {x['n']} ja agendado, pulando"); continue
        arq = baixa(x)
        ps = {k: dict(v) for k, v in ajustes.items() if any(t.startswith(k) for t in alvos)}
        if "youtube" in ps:
            ps["youtube"]["title"] = x["titulo"]
        pid = req("/create-post", {"content": x["c"], "platforms": alvos, "platformSettings": ps})["postGroupId"]
        up = req("/get-upload-url", {"fileName": arq.name, "contentType": "video/mp4",
                                       "type": "video", "postGroupId": pid})
        put = urllib.request.Request(up["uploadUrl"], data=arq.read_bytes(), method="PUT",
                                     headers={"Content-Type": "video/mp4"})
        urllib.request.urlopen(put, timeout=300).read()
        espera_midia(pid)
        req(f"/update-post/{pid}", {"status": "scheduled", "scheduledTime": x["quando"]}, metodo="PUT")
        x["postGroupId"] = pid
        pathlib.Path(a.arquivo).write_text(json.dumps(itens, ensure_ascii=False, indent=1))
        print(f"  {x['n']} agendado {x['quando']} -> {pid}")


if __name__ == "__main__":
    main()
