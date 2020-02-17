
## Development

### JavaScript Development

- Follow the [steps in the Moodle docs](https://docs.moodle.org/dev/Javascript_Modules#How_do_I_write_a_Javascript_module_in_Moodle.3F).
- Depending on the Node.js version, you are using you might want to [comment the lines that check the Node.js version](https://github.com/moodle/moodle/blob/800563e415f64d1cb36bbf9294dc94fdcd6feb84/Gruntfile.js#L41-L45).

When actively developing JavaScript files, use the following command (on Windows) to start the `grunt watch`. Make sure you are in the plugin directory (`moodle/local/learning_anyltics`). This will only watch the plugin directory and apply changes on the fly.

```
grunt watch --root=local/learning_analytics
```

When the development is done, run the following command to generate the `build` directory:

```
grunt amd --root=local/learning_analytics/amd
```