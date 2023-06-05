from flask import Flask, jsonify, request
from PIL import Image
import requests
from io import BytesIO
from tensorflow.keras.utils import img_to_array
import numpy as np
from tensorflow.keras.models import load_model

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    # get image from request
    file = request.files['file']
    img = Image.open(BytesIO(file.read())).resize((150, 150))
    x = img_to_array(img)
    x /= 255
    x = np.expand_dims(x, axis=0)

    # predicting images
    model = load_model('Leukocoria_Cekmate.h5')
    images = np.vstack([x])
    classes = model.predict(images, batch_size=10)

    Leukocoria = np.argmax(classes[0])==0
    if Leukocoria == True:
        response = {"result": "Leukocoria", "confidence": str(max(classes[0]))}
    else:
        response = {"result": "Normal", "confidence": str(max(classes[0]))}
    return jsonify(response)

if __name__ == '__main__':
    app.run(debug=True)