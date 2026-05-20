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