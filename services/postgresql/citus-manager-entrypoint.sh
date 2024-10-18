#!/bin/bash

supported_env_vars=(
    POSTGRES_PASSWORD
    POSTGRES_USER
    POSTGRES_DB
)

for env_var in "${supported_env_vars[@]}"; do
    file_env_var="${env_var}_FILE"
    if [[ -n "${!file_env_var:-}" ]]; then
        if [[ -r "${!file_env_var:-}" ]]; then
            export "${env_var}=$(< "${!file_env_var}")"
            unset "${file_env_var}"
        else
            echo " * Skipping export of '${env_var}'. '${!file_env_var:-}' is not readable."
        fi
    fi
done
unset supported_env_vars

echo " * Starting manager script"
python -u ./manager.py
