#!/usr/bin/env node

import {readFileSync, writeFileSync} from 'node:fs';

const targets = [
    {
        path: 'composer.json',
        pattern: /(?<="version": ")([^"]+)(?=")/
    }
];

const nextVersion = process.argv[2];

for (const target of targets) {
    const content = readFileSync(target.path, {encoding: 'utf-8'});

    if (!target.pattern.test(content)) {
        console.error('Failed to update file', target.path);
        process.exit(1);
    }

    const updated = content.replace(target.pattern, nextVersion);

    writeFileSync(target.path, updated);
}
