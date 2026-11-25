# Local server for the site (repo root), used by the check and og scripts.
#   python3 tools/serve.py        -> http://localhost:8800/
import functools, http.server, pathlib, socketserver, sys, threading

ROOT = pathlib.Path(__file__).resolve().parent.parent


class Quiet(http.server.SimpleHTTPRequestHandler):
    def log_message(self, *a):
        pass


def start(port=0):
    """Serve the repo root in a background thread; returns the base URL."""
    h = functools.partial(Quiet, directory=str(ROOT))
    srv = socketserver.ThreadingTCPServer(('127.0.0.1', port), h)
    srv.daemon_threads = True
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    return f'http://127.0.0.1:{srv.server_address[1]}'


if __name__ == '__main__':
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 8800
    print(start(port) + '/  (Ctrl+C to stop)')
    threading.Event().wait()
