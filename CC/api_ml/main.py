from flask import Flask, jsonify, request #pip install Flask
import json
from PIL import Image #pip install Pillow
import requests #pip install requests
from io import BytesIO
from tensorflow.keras.utils import img_to_array #pip install tensorflow keras
import numpy as np #pip install numpy
from tensorflow.keras.models import load_model
from google.cloud import storage 

app = Flask(__name__)


# create storage client
storage_client = storage.Client()

# define bucket details
bucket_name = 'cekmate_data_deteksi'
bucket = storage_client.bucket(bucket_name)

@app.route('/predict', methods=['POST'])
def predict():
    # get image from request
    file = request.files['file']
    img = Image.open(BytesIO(file.read()))

    # convert RGBA to RGB if necessary
    if img.mode == 'RGBA':
        img = img.convert('RGB')

    # resize and preprocess image
    img = img.resize((150, 150))

    x = img_to_array(img)
    x /= 255
    x = np.expand_dims(x, axis=0)

    # predicting images
    model = load_model('Leukocoria_Cekmate.h5')
    images = np.vstack([x])
    classes = model.predict(images, batch_size=10)

    Leukocoria = np.argmax(classes[0])==0
    if Leukocoria == True:
        response = {"result": "Leukocoria", "confidence": str(max(classes[0])), "file_path": file.filename}
    else:
        response = {"result": "Normal", "confidence": str(max(classes[0])), "file_path": file.filename}
    #return jsonify(response)
    # use json module to preserve key order
    json_response = json.dumps(response, sort_keys=False)
    return json_response

     # upload result to cloud storage
    result_blob = bucket.blob('result.json')
    result_blob.upload_from_string(result_json, content_type='application/json')
    result_blob.acl.all().grant_read()
    result_blob.acl.save()

    # return prediction result
    return jsonify(response)

if __name__ == '__main__':
    app.run(debug=True)
