FROM atendai/evolution-api:v1.8.2

# Ajuste de permissões para evitar erro no Render
USER root

ENV SERVER_PORT=8080
ENV AUTHENTICATION_TYPE=apikey
ENV AUTHENTICATION_API_KEY=SenhaSecretaIgreja123

EXPOSE 8080

