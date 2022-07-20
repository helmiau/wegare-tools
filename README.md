## This Repository Moved To HELMIWRT-PACKAGES
This repository/project is moved to https://github.com/helmiau/helmiwrt-packages/tree/main/luci-app-wegare/root/www/wegare . Update will be resumed at helmiwrt-packages repo. NOT HERE ANYMORE.!

## All In One Installer for Wegare Tools
This is wegare tools, but this is all-in-one. Here is the source <https://github.com/wegare123?tab=repositories>

### Installation
1. Open **Terminal/Putty/JuiceSSH/TTYD/Termius**
2. If you already downloaded older wegare script, you can remove it first by running command below
  ```
[[ -f /bin/wegare ]] && rm -f /bin/wegare
  ```

3. After that, run command below
  ```
wget -q --no-check-certificate "https://raw.githubusercontent.com/helmiau/wegare-tools/main/wegare" -O /bin/wegare && chmod 777 /bin/wegare && /bin/wegare
  ```

Credits
- Wega Regianto
- Nur Adi Saputra
- Muhammad Nabil
- Helmi Amirudin
