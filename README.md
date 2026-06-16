# Remodelação - Website Colégio Crescer

Este projeto é uma versão reformulada do site do **Colégio Crescer**, migrado do WordPress para uma arquitetura limpa de **HTML5, CSS3 e JavaScript (ES6)**. O site inclui integração com a rádio Shoutcast ao vivo e um player de playlist dinâmico alimentado por uma pasta local.

---

## 🚀 Como Acessar Localmente (XAMPP / LAMPP)

Como o projeto está na pasta `/opt/lampp/htdocs/colegio_crescer`, ele já está pronto para rodar no seu servidor local Apache:

1. Inicie o XAMPP/LAMPP:
   ```bash
   sudo /opt/lampp/lampp start
   ```
2. Abra o seu navegador e acesse:
   ```text
   http://localhost/colegio_crescer/
   ```

---

## 🌐 Criando um Host Local Personalizado (VirtualHost)

Para acessar o site com um domínio amigável local (como `colegiocrescer.local`), siga os passos abaixo:

### 1. Habilitar Virtual Hosts no Apache
Abra o arquivo `/opt/lampp/etc/httpd.conf` e procure pela linha:
```apache
# Include etc/extra/httpd-vhosts.conf
```
Remova o `#` (descomente) para ficar:
```apache
Include etc/extra/httpd-vhosts.conf
```

### 2. Adicionar o VirtualHost
Abra o arquivo `/opt/lampp/etc/extra/httpd-vhosts.conf` e adicione a seguinte configuração no final do arquivo:
```apache
<VirtualHost *:80>
    DocumentRoot "/opt/lampp/htdocs/colegio_crescer"
    ServerName colegiocrescer.local
    <Directory "/opt/lampp/htdocs/colegio_crescer">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Configurar o arquivo hosts do sistema
Edite o arquivo `/etc/hosts` da sua máquina:
```bash
sudo nano /etc/hosts
```
Adicione a seguinte linha no final do arquivo:
```text
127.0.0.1    colegiocrescer.local
```

### 4. Reinicie o XAMPP
```bash
sudo /opt/lampp/lampp restart
```
Agora você pode acessar o projeto diretamente em seu navegador digitando: **`http://colegiocrescer.local/`**

---

## 🎵 Pasta de Músicas (`musicas/`)

O site possui um player de áudio integrado com duas aberturas:
1. **Rádio Ao Vivo**: Conecta diretamente à Shoutcast da escola (`https://stm19.xcast.com.br:12814/;`).
2. **Playlist de Músicas**: Lê os arquivos da pasta `/musicas` de forma dinâmica.

### Como Adicionar Músicas:
1. Salve qualquer arquivo de música no formato **`.mp3`** na pasta `/opt/lampp/htdocs/colegio_crescer/musicas/`.
2. O script PHP `/get_songs.php` fará a leitura automática desses arquivos.
3. Ao alternar para a aba "Playlist de Músicas" no player do site, a música aparecerá listada e pronta para tocar.

---

## 📂 Estrutura do Projeto

* `assets/img/` - Imagens baixadas diretamente do site original (logos, fotos da fachada, fotos de salas de aula).
* `assets/video/` - O vídeo institucional (`Crescer.mp4`).
* `musicas/` - Pasta para upload de músicas.
* `index.html` - Página principal estruturada com tags semânticas e otimizações SEO.
* `index.css` - Estilo e visual moderno do site, design responsivo de alta performance e animações.
* `index.js` - Lógica de interatividade (player de rádio e playlist, modal de vídeo, filtros de galeria e menu móvel).
* `get_songs.php` - Script dinâmico PHP que varre a pasta `/musicas` e gera a playlist em JSON para o frontend.
# colegio_crescer
