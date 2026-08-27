from flask import Flask, render_template, jsonify, send_from_directory
import os

app = Flask(__name__)
ASSETS_PATH = os.path.join(app.static_folder, 'assets')

# Categories to include
CATEGORIES = ['Shapes', 'Faces', 'Brows', 'Extras', 'Fun']

def list_pngs(folder):
    return sorted([
        f for f in os.listdir(folder)
        if os.path.isfile(os.path.join(folder, f)) and f.lower().endswith('.png')
    ])

@app.route('/')
def index():
    return render_template('index.html')  # Make sure this is your HTML template

@app.route('/assets-data')
def get_assets_data():
    data = {}

    # Load each category folder into the dictionary
    for category in CATEGORIES:
        folder_path = os.path.join(ASSETS_PATH, category)
        data[category] = list_pngs(folder_path) if os.path.exists(folder_path) else []

    # Load palettes, organized by shape
    palettes_folder = os.path.join(ASSETS_PATH, 'Palettes')
    palette_sets = {}

    if os.path.exists(palettes_folder):
        for subfolder in os.listdir(palettes_folder):
            subfolder_path = os.path.join(palettes_folder, subfolder)
            if os.path.isdir(subfolder_path):
                shape_key = subfolder.replace('Palettes', '').lower()
                palette_sets[shape_key] = list_pngs(subfolder_path)

    data['Palettes'] = palette_sets
    return jsonify(data)

# New route to serve static files
@app.route('/static/<path:filename>')
def static_files(filename):
    return send_from_directory('static', filename)

if __name__ == '__main__':
    app.run(debug=True)
