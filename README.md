The frontend has been used as a based by https://startbootstrap.com/themes/sb-admin-2/

BarCodeGenerator needs Imagick
- yum install perl-devel
- Install sudo pecl install imagick

download Exiftool https://exiftool.org/index.html (atm 12.01)
cd /tar-folder
yum install perl-devel
perl Makefile.PL
make test
sudo make install

FPDF Version: 1.82 http://www.fpdf.org/
Not used atm...

MPDF version 7.X https://mpdf.github.io/
HTML pages to PDF
- https://mpdf.github.io/real-life-examples/pdf-from-every-page-of-website.html
- No need to install. Included to the package


### Local development
1. If `.env` does not exist, run `cp -n .env.example .env`. Keep `DB_HOST=db`; it is the Compose MySQL service name.
2. Start the stack: `podman-compose up -d --build`. On the first start with an empty `mysql-data` volume, MySQL imports `massi_prod_backup_2026-10-15.sql` automatically.
3. Open http://localhost:8080. MySQL is available only on this machine at `localhost:3306` (database `digitointi`, user/password from `.env`).

The dump is imported only when MySQL initializes a new data volume. To recreate the local database from the dump, stop the stack and remove its volume with `podman-compose down -v`, then start it again. This permanently deletes the database data stored in that Compose volume.

To stop the containers without deleting the database, run `podman-compose down`.
