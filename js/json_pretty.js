const fs = require('node:fs');
const file = process.argv[2]

fs.readFile(file, 'utf8', (err, data) => {
  if (err) {
    console.error(err);
    return;
  }
  const str = console.log(JSON.parse(data))
  console.log(JSON.stringify(str, null, 2))
});
