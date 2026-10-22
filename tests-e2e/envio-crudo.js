// @ts-check
import net from 'node:net';

/**
 * POST "a mano" por un socket, para simular que se corta internet A MITAD del envío:
 * se anuncia el tamaño completo (Content-Length) pero solo se manda una parte del
 * cuerpo y se cierra la conexión, como pasa cuando el equipo pierde la red.
 *
 * @param {object} o
 * @param {string} o.url        URL completa (http://host:puerto/ruta)
 * @param {string} o.cookie     Cabecera Cookie con la sesión
 * @param {string} o.tipo       Content-Type
 * @param {Buffer} o.cuerpo     Cuerpo completo
 * @param {number} [o.enviar]   Bytes del cuerpo que llegan a salir (sin valor: todos)
 * @returns {Promise<{respondio: boolean, texto: string}>}
 */
export function envioCrudo({ url, cookie, tipo, cuerpo, enviar }) {
  const u = new URL(url);
  const cortar = enviar !== undefined && enviar < cuerpo.length;
  return new Promise((resolver, rechazar) => {
    // Sin forzar 127.0.0.1: en Windows el servidor de pruebas puede escuchar solo en ::1
    const socket = net.connect({ port: Number(u.port || 80), host: u.hostname, autoSelectFamily: true });
    let texto = '';
    let conecto = false;
    socket.on('data', (d) => { texto += d.toString('utf8'); });
    // Si nunca hubo conexión, el corte no se probó: es un error, no un "no respondió"
    socket.on('error', (e) => (cortar && conecto ? resolver({ respondio: texto !== '', texto }) : rechazar(e)));
    socket.on('close', () => resolver({ respondio: texto !== '', texto }));
    socket.on('connect', () => {
      conecto = true;
      socket.write(
        `POST ${u.pathname}${u.search} HTTP/1.1\r\n`
        + `Host: ${u.host}\r\n`
        + `Cookie: ${cookie}\r\n`
        + `Content-Type: ${tipo}\r\n`
        + `Content-Length: ${cuerpo.length}\r\n`
        + 'Connection: close\r\n\r\n',
      );
      if (!cortar) {
        socket.write(cuerpo);
        return;
      }
      socket.write(cuerpo.subarray(0, enviar));
      // Corte: se destruye la conexión sin mandar el resto
      setTimeout(() => socket.destroy(), 300);
    });
  });
}

/** Cookie de sesión del navegador de la prueba, lista para la cabecera. */
export async function cookieDeSesion(page) {
  const cookies = await page.context().cookies();
  return cookies.map((c) => `${c.name}=${c.value}`).join('; ');
}

/** Cuerpo application/x-www-form-urlencoded (en el orden dado). */
export function cuerpoFormulario(campos) {
  return Buffer.from(new URLSearchParams(campos).toString(), 'utf8');
}

/** Cuerpo multipart/form-data (en el orden dado). Devuelve { tipo, cuerpo }. */
export function cuerpoMultipart(campos) {
  const limite = '----PruebaCorte' + Date.now();
  const partes = Object.entries(campos).map(([nombre, valor]) =>
    `--${limite}\r\nContent-Disposition: form-data; name="${nombre}"\r\n\r\n${valor}\r\n`);
  return {
    tipo: `multipart/form-data; boundary=${limite}`,
    cuerpo: Buffer.from(partes.join('') + `--${limite}--\r\n`, 'utf8'),
  };
}
