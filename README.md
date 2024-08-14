# Plugin Azure Storage Cache Backup

Este plugin para WordPress permite que você faça backup do cache do seu site no Azure Storage. Ele é fácil de configurar e oferece uma maneira segura de armazenar os dados de cache na nuvem.

## Funcionalidades

- Backup do cache do WordPress para o Azure Storage
- Configuração simples com a conta de armazenamento Azure
- Suporte a nomes de containers personalizados

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.2 ou superior
- Uma conta de armazenamento no Azure

## Instalação

1. **Carregar o Plugin:**
   - Baixe o plugin do [repositório GitHub](https://github.com/luizreimann/azure-storage-cache-backup).
   - Envie os arquivos do plugin para o diretório `/wp-content/plugins/` ou instale o plugin diretamente pela tela de plugins do WordPress.

2. **Ativar o Plugin:**
   - Vá para a tela 'Plugins' no WordPress e ative o plugin `Azure Storage Cache Backup`.

3. **Configurar o Plugin:**
   - Após a ativação, navegue até `Configurações > Azure Storage Cache Backup`.
   - Preencha os campos necessários:
     - **Nome da Storage Account**: O nome da sua conta de armazenamento no Azure.
     - **Chave da Storage Account**: A chave de acesso da sua conta de armazenamento no Azure.
     - **Nome do Container**: O nome do container onde os backups serão armazenados.

4. **Criar uma Conta de Armazenamento no Azure:**
   - Se você ainda não tem uma conta de armazenamento no Azure, pode criar uma se inscrevendo em [Azure.com](https://azure.com).
   - Siga a documentação do Azure para criar uma Storage Account e gerar as chaves necessárias.

## Uso

Uma vez configurado, o plugin fará automaticamente o backup do cache no container especificado no Azure Storage. Certifique-se de que os detalhes da sua conta de armazenamento no Azure estão corretos para garantir que os backups sejam realizados com sucesso.

## Contribuição

Contribuições para este projeto são bem-vindas. Sinta-se à vontade para enviar pull requests ou abrir issues no [GitHub](https://github.com/luizreimann/azure-storage-cache-backup).

## Licença

Este projeto é licenciado sob a Licença GNU v3. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## Suporte

Para qualquer problema ou dúvida, por favor, abra uma issue no [GitHub](https://github.com/luizreimann/azure-storage-cache-backup/issues).

---

**Nota:** Este plugin é fornecido como está, sem garantias. Certifique-se de testá-lo completamente antes de usá-lo em um ambiente de produção.
