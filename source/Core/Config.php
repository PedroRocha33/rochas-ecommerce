<?php

/*
|--------------------------------------------------------------------------
| BANCO DE DADOS
|--------------------------------------------------------------------------
*/
const CONF_DB_HOST = "localhost";
const CONF_DB_NAME = "ecommerce";
const CONF_DB_USER = "root";
const CONF_DB_PASSWORD = "";

/*
|--------------------------------------------------------------------------
| URL BASE
|--------------------------------------------------------------------------
*/
const CONF_URL_BASE = "http://localhost/rochas";

/*
|--------------------------------------------------------------------------
| UPLOAD DE IMAGENS
|--------------------------------------------------------------------------
| Regras mais flexíveis, sem burocracia excessiva
*/
const IMAGE_MAX_SIZE = 10 * 1024 * 1024; // 10MB
const IMAGE_MIN_SIZE = 1 * 1024;         // 1KB

const IMAGE_DIR = "/storage/images";

const ALLOWED_IMAGE_TYPES = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp"
];

/*
|--------------------------------------------------------------------------
| UPLOAD DE ARQUIVOS
|--------------------------------------------------------------------------
*/
const FILE_MAX_SIZE = 20 * 1024 * 1024; // 20MB
const FILE_MIN_SIZE = 1 * 1024;         // 1KB

const FILE_DIR = "/storage/files";

const ALLOWED_FILE_TYPES = [
    "application/pdf",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/vnd.ms-excel",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "text/plain"
];

/*
|--------------------------------------------------------------------------
| MENSAGENS DE ERRO
|--------------------------------------------------------------------------
*/
const IMAGE_SIZE_ERROR_MESSAGE = "Imagem inválida. O tamanho deve ser até 10MB.";
const IMAGE_TYPE_ERROR_MESSAGE = "Imagem inválida. Formatos permitidos: JPG, PNG ou WEBP.";
const IMAGE_MOVE_ERROR_MESSAGE = "Erro ao salvar a imagem.";

const FILE_SIZE_ERROR_MESSAGE = "Arquivo inválido. O tamanho deve ser até 20MB.";
const FILE_TYPE_ERROR_MESSAGE = "Tipo de arquivo não permitido.";
const FILE_MOVE_ERROR_MESSAGE = "Erro ao salvar o arquivo.";
