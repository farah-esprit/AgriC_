import sys
import json
import pandas as pd
from sklearn.linear_model import LinearRegression
import numpy as np
from datetime import datetime, timedelta

def predict_demand():
    # Read data from stdin (JSON format)
    try:
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"error": "No input data"}))
            return
            
        data = json.loads(input_data)
        if not data:
            print(json.dumps({"result": 0, "message": "Insufficient data"}))
            return
            
        # Convert to DataFrame
        df = pd.DataFrame(data)
        
        # Ensure 'date' is datetime
        df['date'] = pd.to_datetime(df['date'])
        
        # Aggregate by date (sum quantity if multiple orders on same day)
        df = df.groupby('date')['quantity'].sum().reset_index()
        
        if len(df) < 2:
            # Need at least 2 points for a line, otherwise return mean or last
            result = int(df['quantity'].iloc[0]) if len(df) == 1 else 0
            print(json.dumps({"result": result, "message": "Limited historical data"}))
            return

        # Prepare features: days since start
        start_date = df['date'].min()
        df['days'] = (df['date'] - start_date).dt.days
        
        X = df[['days']].values
        y = df['quantity'].values
        
        # Train model
        model = LinearRegression()
        model.fit(X, y)
        
        # Predict for next 30 days
        last_day = df['days'].max()
        future_days = np.array([[last_day + i] for i in range(1, 31)])
        predictions = model.predict(future_days)
        
        # Return total sum for coming month, clamped at 0
        total_prediction = max(0, int(np.sum(predictions)))
        
        print(json.dumps({
            "result": total_prediction,
            "coefficient": float(model.coef_[0]),
            "intercept": float(model.intercept_)
        }))
        
    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    predict_demand()
