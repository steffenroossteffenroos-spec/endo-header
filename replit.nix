{ pkgs }: {
deps = [
    pkgs.php84
    pkgs.php84Extensions.curl
    pkgs.php84Extensions.openssl
  ];
}
